<?php
namespace Bitmotion\BmImageGallery\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Rene Fritz <typo3-ext@bitmotion.de>, Bitmotion
 *  (c) 2016 Florian Wessels <typo3-ext@bitmotion.de>, Bitmotion
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use Bitmotion\BmImageGallery\Domain\Model\Dto\CollectionInfo;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;


/**
 * @package bm_image_gallery
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ListController extends ActionController
{

    /**
     * @var \TYPO3\CMS\Core\Resource\FileCollectionRepository
     * @inject
     */
    protected $fileCollectionRepository = null;

    /**
     * action default
     *
     * @return void
     */
    public function defaultAction()
    {
        $showOverview = false;

        // $this->settings is set with flexform values

        if ($this->settings['collections']) {
            $collectionUids = GeneralUtility::trimExplode(',', $this->settings['collections'], true);

            $showOverview = count($collectionUids) > 1 ? true : false;

            if (!$showOverview && ($this->settings['listPid'] > 0)) {
                $showOverview = true;
            }

            if (array_key_exists('show', $this->request->getArguments())) {
                if ($this->request->getArgument('show') != '') {
                    if (in_array($this->request->getArgument('show'), $collectionUids)) {
                        $this->forward('list');
                    }
                }
            }

            if ($showOverview) {
                $this->forward('overview');
            } else {
                $this->forward('list');
            }
        }
    }


    /**
     * action overview
     *
     * @return void
     */
    public function overviewAction()
    {
        $collectionInfoObjects = [];

        if ($this->settings['collections']) {
            $collectionUids = GeneralUtility::trimExplode(',', $this->settings['collections'], true);

            foreach ($collectionUids as $collectionUid) {
                try {
                    $fileCollection = $this->fileCollectionRepository->findByUid($collectionUid);
                    if ($fileCollection instanceof AbstractFileCollection) {
                        $fileCollection->loadContents();
                        $fileObjects = $fileCollection->getItems();
                        $fileObjects = $this->cleanFiles($fileObjects);

                        $collectionInfo = new CollectionInfo();
                        $collectionInfo->setIdentifier($collectionUid);
                        $collectionInfo->setTitle($fileCollection->getTitle());
                        $collectionInfo->setDescription($fileCollection->getDescription());
                        $collectionInfo->setItemCount(count($fileObjects));
                        /** @var File $fileObject */
                        $fileObject = reset($fileObjects);
                        $collectionInfo->setPreview($fileObject);

                        $collectionInfoObjects[] = $collectionInfo;
                    }
                } catch (Exception $e) {
                    /** @var Logger $logger */
                    $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger();
                    $logger->warning('The file-collection with uid  "' . $collectionUid . '" could not be found or contents could not be loaded and won\'t be included in frontend output');
                }
            }
        }

        $this->view->assign('items', $collectionInfoObjects);
    }


    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $fileObjects = [];
        $collectionUidsCount = 0;
        $fileCollection = '';

        if ($this->settings['collections']) {
            $collectionUids = GeneralUtility::trimExplode(',', $this->settings['collections'], true);

            $collectionUidsCount = count($collectionUids);

            if ($this->request->hasArgument('show')) {
                $showUid = $this->request->getArgument('show');

                if (in_array($showUid, $collectionUids)) {
                    $collectionUids = [$showUid];
                }
            }

            foreach ($collectionUids as $collectionUid) {
                try {
                    $fileCollection = $this->fileCollectionRepository->findByUid($collectionUid);
                    if ($fileCollection instanceof AbstractFileCollection) {
                        $fileCollection->loadContents();

                        $this->addToArray($fileCollection->getItems(), $fileObjects);
                    }
                } catch (Exception $e) {
                    /** @var Logger $logger */
                    $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger();
                    $logger->warning('The file-collection with uid  "' . $collectionUid . '" could not be found or contents could not be loaded and won\'t be included in frontend output');
                }
            }

            foreach ($fileObjects as $key => $file) {
                // file collection returns different types depending on the static or folder type
                if ($file instanceof FileReference) {
                    $fileObjects[$key] = $file->getOriginalFile();
                }
            }
        }

        $fileObjects = $this->cleanFiles($fileObjects);

        $this->view->assignMultiple([
            'contentId' => $this->configurationManager->getContentObject()->data['uid'],
            'collectionCount' => $collectionUidsCount,
            'title' => $fileCollection->getTitle(),
            'description' => $fileCollection->getDescription(),
            'itemCount' => count($fileObjects),
            'items' => $fileObjects,
        ]);
    }


    /**
     * Adds $newItems to $theArray, which is passed by reference. Array must only consist of numerical keys.
     *
     * @param mixed $newItems Array with new items or single object that's added.
     * @param array $theArray The array the new items should be added to. Must only contain numeric keys (for
     *     array_merge() to add items instead of replacing).
     */
    protected function addToArray($newItems, array &$theArray)
    {
        if (is_array($newItems)) {
            $theArray = array_merge($theArray, $newItems);
        } elseif (is_object($newItems)) {
            $theArray[] = $newItems;
        }
    }


    /**
     * http://forge.typo3.org/issues/58806
     *
     * @param $fileObjects
     *
     * @return array
     */
    protected function cleanFiles($fileObjects)
    {
        $tmpArray = [];
        foreach ($fileObjects as $file) {
            /** @var File $file */
            $tmpArray[$file->getProperty('uid')] = $file;
        }

        return $tmpArray;
    }
}

?>