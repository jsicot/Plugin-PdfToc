<?php
/**
 * PDF Toc based on Pdf Text Plugin by Roy Rosenzweig Center for History and New Media
 * 
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * @package Omeka\Plugins\PdfToc
 */
class PdfTocProcess extends Omeka_Job_AbstractJob
{
    /**
     * Process all PDF files in Omeka.
     */
    public function perform()
    {
        $pdfTocPlugin = new PdfTocPlugin;
        $fileTable = $this->_db->getTable('File');

        $select = $this->_db->select()
            ->from($this->_db->File)
            ->where('mime_type IN (?)', $pdfTocPlugin->getPdfMimeTypes());

        // Iterate all PDF file records.
        $pageNumber = 1;
        while ($files = $fileTable->fetchObjects($select->limitPage($pageNumber, 50))) {
            foreach ($files as $file) {

                // Delete any existing PDF toc element table of contents from the file.
                $textElement = $file->getElement(
                    PdfTocPlugin::ELEMENT_SET_NAME,
                    PdfTocPlugin::ELEMENT_NAME
                );
                $file->deleteElementTextsByElementId(array($textElement->id));

                // Extract the PDF toc and add it to the file.
                $file->addTextForElement(
                    $textElement,
                    $pdfTocPlugin->pdfToc(FILES_DIR . '/original/' . $file->filename)
                );
                $file->save();

                // Prevent memory leaks.
                release_object($file);
            }
            $pageNumber++;
        }
    }
}
