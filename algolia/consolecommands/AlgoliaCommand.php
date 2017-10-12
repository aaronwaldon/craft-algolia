<?php

/**
 * Algolia plugin for Craft CMS.
 *
 * algolia command
 *
 */

namespace Craft;

class AlgoliaCommand extends BaseCommand
{
    /**
     * Command: php {script_name_here} algolia clearindex --name=some_index_name_here
     */
    public function actionClearIndex($name)
    {
        $index = craft()->algolia->getAlgoliaClient()->initIndex($name);
        $index->clearIndex();
    }

    /**
     * Imports all mapping indexes.
     *
     * Command: php {script_name_here} algolia import
     *
     * @param int $index
     * @param int $page
     * @return mixed
     */
    public function actionImport($index = -1, $page = 0)
    {
        //if this is the first time being called, kick off separate commands for each of the mappings
        if($index < 0){

            $this->writeLog('Starting Algolia importation.');

            foreach (craft()->algolia->getMappings() as $algoliaIndexModel) {

                $this->writeLog('Importing index '. $algoliaIndexModel->indexName);
                $command = strtr('php {script} algolia import --index="{index}" --page="{page}"', [
                    '{script}' => $this->getCommandRunner()->getScriptName(),
                    '{index}' => ++$index,
                    '{page}' => 0
                ]);

                passthru($command);
            }

            $this->writeLog('Import done.');

            return craft()->end();
        }

        //break up the elements into batches and import each batch
        $pageInfo = $this->paginateImportation($index);

        foreach($pageInfo['pages'] as $page)
        {
            $this->writeLog('Importing page ' .( $page +1 ). '/'.$pageInfo['totalPages']. ' of index '.$index );

            $command = strtr('php {script} algolia importIndexPage --index="{index}" --page="{page}"', [
                '{script}' => $this->getCommandRunner()->getScriptName(),
                '{index}' => $index,
                '{page}' => $page
            ]);

            passthru($command);
        }

        craft()->end();
    }

    /**
     * Calls the import method of the Algolia service to index
     * a batch of elements for the specified mapping index.
     *
     * @param $index
     * @param $page
     */
    public function actionImportIndexPage($index, $page)
    {
        craft()->algolia->import($index, $page);
    }

    /**
     * Paginates the import of a mapping index.
     *
     * @param $index
     * @return array
     */
    private function paginateImportation($index)
    {
        $limit = craft()->config->get('limit', 'algolia');

        $currentIndex = array_slice(craft()->algolia->getMappings(), $index, 1);
        $currentIndex = array_shift($currentIndex);

        $criteria = craft()->elements->getCriteria(ucfirst($currentIndex->elementType), $currentIndex->elementCriteria);
        $criteria->status = null;
        $criteria->limit = null;
        $total = $criteria->total();

        $this->writeLog( 'Total elements ' . $total);

        $totalPages = ceil( $total / $limit );

        return ['totalPages' => $totalPages, 'pages'=>range(0, $totalPages)];

    }

    /**
     * Outputs a string to the console.
     *
     * @param $str
     */
    private function write($str)
    {
        echo $str;
    }

    /**
     * Outputs a line to the console.
     *
     * @param string $str
     */
    private function writeLn($str = '')
    {
        echo (is_array($str) ? implode(PHP_EOL, $str) : $str) . PHP_EOL;
    }

    /**
     * Logs a message to the console.
     *
     * @param $str
     * @param bool $ln
     */
    private function writeLog($str, $ln = true)
    {
        $now = new DateTime('now');

        $log = sprintf(
            '%s - %s (%s M)',
            $now->mySqlDateTime(),
            is_array($str) ? implode(PHP_EOL, $str) : $str,
            round(memory_get_usage() / 1024 / 1024, 2)
        );

        $ln ? $this->writeLn($log) : $this->write($log);
    }
}