<?php

class PdfTocProcess extends ProcessAbstract
{
    public function run($args)
    {
        $db = get_db();

        $pdfToc = new PdfTocPlugin;
        $elementId = $pdfToc->getPdfTocElementId();
        $recordTypeId = $pdfToc->getItemRecordTypeId();

        // Fetch all items as an array and iterate them. Fetching objects here 
        // would risk reaching PHP's memory limit.
        $sql = "SELECT * FROM {$db->Item}";
        $items = $db->fetchAll($sql);
        foreach ($items as $i) {

            // Release an existing item to avoid a memory leak in PHP 5.2.
            if (isset($item)) {
                release_object($item);
            }

            $item = $db->getTable('Item')->find($i['id']);
            $item->deleteElementTextsByElementId(array($elementId));
            $pdfToc->saveItemPdfToc($item);
            _log("Enregistrement TOC de la notice ".$i['id']);
        }
    }
}


?>
