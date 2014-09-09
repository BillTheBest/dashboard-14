<?php
namespace OCA\Dashboard\Service;

class StatService {

    protected $userManager;
    protected $datas;

    public function __construct($userManager) {
        $this->userManager = $userManager;

        $this->datas = array();
    }

    public function getUserDataDir() {
        return \OCP\Config::getSystemValue('datadirectory', '');
    }

    public function countUsers() {
        if (isset($this->datas['nbUsers'])) {
            return $this->datas['nbUsers'];
        }

        $nbUsers = 0;

        $nbUsersByBackend = $this->userManager->countUsers();

        if (!empty($nbUsersByBackend) and is_array($nbUsersByBackend)) {
            foreach($nbUsersByBackend as $backend => $count) {
                $nbUsers += $count;
            }
        }

        $this->datas['nbUsers'] = $nbUsers;

        return $nbUsers;
    }

    public function getGlobalStorageInfo() {
        $view = new \OC\Files\View();
        $stats = array();
        $stats['totalFiles'] = 0;
        $stats['totalFolders'] = 0;
        $stats['totalShares'] = 0;
        $stats['totalSize'] = 0;
        $stats['users'] = array();
        $stats['defaultQuota'] = \OCP\Util::computerFileSize(\OCP\Config::getAppValue('files', 'default_quota', 'none'));

        $nbFoldersVariance = new Variance;
        $nbFilesVariance = new Variance;
        $nbSharesVariance = new Variance;

        $this->getFilesStat($view, '', $stats);

        $stats['totalFolders'] -= $this->countUsers();

        // some basic stats
        $stats['filesPerUser'] = $stats['totalFiles'] / $this->countUsers();
        $stats['filesPerFolder'] = $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = $stats['totalFolders'] / $this->countUsers();
        $stats['sharesPerUser'] = $stats['totalShares'] / $this->countUsers();
        $stats['sizePerUser'] = $stats['totalSize'] / $this->countUsers();
        $stats['sizePerFile'] = $stats['totalSize'] / $stats['totalFiles'];
        $stats['sizePerFolder'] = $stats['totalSize'] / $stats['totalFolders'];

        foreach($stats['users'] as $owner => $datas) {
            $nbFoldersVariance->addValue($data['nbFolders']);
            $nbFilesVariance->addValue($datas['nbFiles']);

            // shares
            $stats['users'][$owner]['nbShares'] = $this->getSharesStats($owner);
            $stats['totalShares'] += $stats['users'][$owner]['nbShares'];
            $nbSharesVariance->addValue($stats['users'][$owner]['nbShares']);
        }

        $stats['sharesPerUser'] = $stats['totalShares'] / $this->countUsers();

        $stats['meanNbFilesPerUser'] = $nbFilesVariance->getMean();
        $stats['stdvNbFilesPerUser'] = $nbFilesVariance->getStandardDeviation();
        $stats['stdvNbFoldersPerUser'] = $nbFoldersVariance->getStandardDeviation();
        $stats['stdvNbsharesPerUser'] = $nbSharesVariance->getStandardDeviation();

        return $stats;
    }

    /**
     * Get some global stats
     * @param \OC\Files\View $view
     * @param string $path the path
     * @param mixed $stats array to store the extrated stats
     */
    protected function getFilesStat($view, $path='', &$stats) {
        $dc = $view->getDirectoryContent($path);
        foreach($dc as $item) {
            $owner = $this->getOwner($item->getPath());

            if (!isset($stats['users'][$owner])) {
                if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                    $stats['users'][$owner] = array();
                    $stats['users'][$owner]['nbFiles'] = 0;
                    $stats['users'][$owner]['nbFolders'] = 0;
                    $stats['users'][$owner]['nbShares'] = 0;
                    $stats['users'][$owner]['filesize'] = 0;
                    $stats['users'][$owner]['quota'] = \OC_Util::getUserQuota($owner);
                }
                else {
                    // do not get files in rootDir
                    continue;
                }
            }

            if ($item->isShared()) {
                continue;
            }

            // if folder, recurse
            if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                $stats['totalFolders']++;
                $stats['users'][$owner]['nbFolders']++;
                $this->getFilesStat($view, $item->getPath(), $stats);
            }
            else {
                $stats['users'][$owner]['nbFiles']++;
                $stats['totalFiles']++;
                $stats['users'][$owner]['filesize'] += $item->getSize();
                $stats['totalSize'] += $item->getSize();
            }
        }
    }

    /**
     * Dirty function to extract owner from filepath
     * @param string $path
     * @return string owner of this filepath
     */
    protected function getOwner($path) {
        // admin files seem to begin with "//"
        if (strpos($path, "//") === 0) {
            return str_replace("//", "", $path);
        }

        preg_match("#^/([^/]*)/.*$#", $path, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }

    protected function getSharesStats($owner) {
        // $shares = \OCP\Share::getItemsSharedWithUser('file', 'admin');
        $sharedFiles   = \OC\Share\Share::getItems('file', null, null, null, $owner, \OC\Share\Share::FORMAT_NONE, null, -1, false);
// $f = fopen('/tmp/truc.log', 'a');
// fputs($f, $owner . " : files\n");
// fputs($f, print_r($sharedFiles, true) . "\n");
        //  $sharedFolders = \OC\Share\Share::getItems('folder', null, null, null, $owner, \OC\Share\Share::FORMAT_NONE, null, -1, false);
// fputs($f, $owner . " : folders\n");
// fputs($f, print_r($sharedFolders, true) . "\n");
// fclose($f);

        return count($sharedFiles)/* + count($sharedFolders)*/;
    }
}
