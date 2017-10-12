<?php
namespace Craft;

class AlgoliaCommand extends BaseCommand
{
    public function actionImport($indice = -1, $page = 0)
    {

        if($indice < 0){

            $this->writeLog('Starting Algolia importation.');

            foreach (craft()->algolia->getIndicies() as $indexName => $index) {

                $this->writeLog('Importing indice '. $indexName);
                $command = strtr('php {script} algolia import --indice="{indice}" --page="{page}"', [
                    '{script}' => $this->getCommandRunner()->getScriptName(),
                    '{indice}' => ++$indice,
                    '{page}' => 0
                ]);

                passthru($command);
            }

            $this->writeLog('Import done.');

            return craft()->end();
        }

        $pageInfo = $this->paginateImportation($indice);

        foreach($pageInfo['pages'] as $page)
        {
            $this->writeLog('Importing page ' .( $page +1 ). '/'.$pageInfo['totalPages']. ' of index '.$indice );

            $command = strtr('php {script} algolia importIndicePage --indice="{indice}" --page="{page}"', [
                '{script}' => $this->getCommandRunner()->getScriptName(),
                '{indice}' => $indice,
                '{page}' => $page
            ]);

            passthru($command);
        }

        craft()->end();

    }

    public function actionImportIndicePage($indice, $page)
    {
        craft()->algolia->import($indice, $page);
    }

    private function paginateImportation($index)
    {
        $limit = craft()->config->get('limit', 'algolia');

        $currentIndex = array_slice(craft()->algolia->getIndicies(), $index, 1);
        $currentIndex = array_shift($currentIndex);

        $criteria = craft()->elements->getCriteria(ucfirst($currentIndex->elementType), $currentIndex->elementCriterias);
        $criteria->status = null;
        $criteria->limit = null;
        $total = $criteria->total();

        $this->writeLog( 'Total elements ' . $total);

        $totalPages = ceil( $total / $limit );

        return ['totalPages' => $totalPages, 'pages'=>range(0, $totalPages)];

    }

    private function write($str)
    {
        echo $str;
    }

    private function writeLn($str = '')
    {
        echo (is_array($str) ? implode(PHP_EOL, $str) : $str) . PHP_EOL;
    }

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
