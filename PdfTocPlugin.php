<?php
/**
 * Make PDF ToC available in Omeka.
 * 
 * derivated from PdfSearch plugin.
 */
class PdfTocPlugin extends Omeka_Plugin_Abstract
{
    const ELEMENT_SET_NAME = 'PDF Table Of Content';
    const ELEMENT_NAME = 'Text';
    
    protected $_hooks = array('install', 'uninstall', 'after_save_item', 
                              'after_delete_file', 'config', 'config_form');
   
    protected $_pdfMimeTypes = array('application/pdf', 'application/x-pdf', 
                                     'application/acrobat', 'text/x-pdf', 
                                     'text/pdf', 'applications/vnd.pdf');
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Don't install if a PDF Search element set already exists.
        if ($this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)) {
            throw new Exception('An element set by the name "' . self::ELEMENT_SET_NAME . '" already exists. You must delete that element set to install this plugin.');
        }
        
        // Don't install if the pdftk command doesn't exist.
        $output = (int) shell_exec('hash pdftk 2>&- || echo -1');
        if (-1 == $output) {
            throw new Exception('The pdftk command-line utility is not installed. pdftotext must be installed to install this plugin.');
        }
        
        // Insert the element set.
        $elementSetMetadata = array('name' => self::ELEMENT_SET_NAME, 
                                    'description' => 'This element set enables storing TOC od PDF files.');
        $elements = array(array('name' => self::ELEMENT_NAME, 
                                'description' => 'TOC extracted from PDF files belonging to this item. One line per element, looking like page|title'));
        insert_element_set($elementSetMetadata, $elements);
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Delete the PDF Search element set.
        $this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)->delete();
    }
    
    /**
     * Refresh PDF texts to account for an item save.
     */
    public function hookAfterSaveItem($item)
    {
        # print $_POST['force-toc-update'];
        $this->saveItemPdfToc($item);
    }
    
    /**
     * Refresh PDF texts to account for a file delete.
     */
    public function hookAfterDeleteFile($file)
    {
        $item = $file->getItem();
        $this->saveItemPdfToc($item);
    }

		public function hookConfigForm()
		{
	?>
			<div class="field">
				<label for="save_pdf_tocs">Traiter tous les fichiers PDF</label>
				<div class="inputs">
					<?php echo __v()->formCheckbox('extract_pdf_tocs'); ?>
				</div>
				<p class="explanation">
					Ce plugin fait normalement la mise à jour des tables de matières à chaque mise à jour de document. Pour une raison ou pour une autre, il peut etre nécessaire de tout remettre à zéro. Pour cela cocher la base ci-dessus et "Sauvegarder les changements"
				</p>
			</div> <!-- #field -->
	<?php
		}

    /**
    * Process the plugin config form.
    */
    public function hookConfig()
		{
			// Run the text extraction process if directed to do so.
			if ($_POST['extract_pdf_tocs']) {
				ProcessDispatcher::startProcess('PdfTocProcess');
			}
		}

    /**
     * Extract TOC from PDF file, and stores it into the specific field of the parent item
     */
    
    public function saveItemPdfToc(Item $item)
    {
        $element = $item->getElementByNameAndSetName(self::ELEMENT_NAME, self::ELEMENT_SET_NAME);
        $recordTypeId = $this->_db->getTable('RecordType')->findIdFromName('item');

        // Iterate the files.
        foreach ($item->Files as $file) {
            
            // Ignore all other file types.
            if (!in_array($file->mime_browser, $this->_pdfMimeTypes)) {
                continue;
            }
            
            // Extract the text.
            $path = escapeshellarg(FILES_DIR . '/' . $file->archive_filename);
            $cmd = "pdftk $path dump_data";
            $dump_data = shell_exec($cmd);
            
            /* Starging there, we've extracted the dump_data, which looks like :
                InfoKey: Creator
                InfoValue: I.C.S. v1.2.7.1 Copyright &#169;  2009-2011 ISAKO
                InfoKey: Producer
                InfoValue: iTextSharp 5.0.5 (c) 1T3XT BVBA
                InfoKey: ModDate
                InfoValue: D:20111109130842+01'00'
                InfoKey: CreationDate
                InfoValue: D:20110722161049+02'00'
                PdfID0: 6c67c63a772113a953554635eb161a
                PdfID1: c828f9cd1b17e34aa3a0254447f13a97
                NumberOfPages: 44
                BookmarkTitle: LISTE PAR ORDRE ALPHAB&#201;TIQUE DES HOMMES DE SANG
                BookmarkLevel: 1
                BookmarkPageNumber: 3
                BookmarkTitle: A
                BookmarkLevel: 2
                BookmarkPageNumber: 3
            
            We're interested by things starting at BookmarkTitle. we need to extract these to create something like this:
            Level|Title|PageNumber
            */
            $item->deleteElementTextsByElementId(array($element->id));
            // We remove anything before the first bookmark
            $dump_data = preg_replace("/^.*(Bookmark.*)$/isU", "$1", $dump_data);
            $dump_data_array = preg_split("/\n/", $dump_data);

            $textToc = "";
            for ($i = 0; $i <= sizeof($dump_data_array); $i+=3)
            {
                $bm_title   = str_replace("BookmarkTitle: ", "", $dump_data_array[$i]);
                $bm_level   = str_replace("BookmarkLevel: ", "", $dump_data_array[$i+1]);
                $bm_page    = str_replace("BookmarkPageNumber: ", "", $dump_data_array[$i+2]);
                if ($textToc != "")
                {
                    $textToc .= "\n";
                }
                if ( ($bm_level != "") and ($bm_title != "") and ($bm_page != "") )
                {
                    $textToc .= $bm_level."|".$bm_title."|".$bm_page;
                }
            }
            // Save the TOC;
            $tocRecord = new ElementText;
            $tocRecord->record_id = $item->id;
            $tocRecord->element_id = $element->id;
            $tocRecord->record_type_id = $recordTypeId;
            $tocRecord->text = $textToc;
            $tocRecord->html = false;
            $tocRecord->save();
        }
    }    
    
    
    /* We had a checkbox, allowing user to force the update of the TOC.
    */
    public static function forceUpdateToc($html, $inputNameStem, $value)
    {
        ob_start();
?>
        <textarea name="<?php echo $inputNameStem; ?>[text]" class="textinput" rows="15" cols="50"><?php echo $value; ?></textarea>
        <div style='font-weight:bold; padding-bottom:10px; border-bottom:2px solid #EAE9DB; margin-bottom:10px;'>
            <input id='force-toc-update' name='force-toc-update' type='checkbox' style='padding:0px; margin:0px;'/>&nbsp;Forcer la mise à jour de la table des matières
        </div>
        
<?php
        return ob_get_clean();
    }
    
    /**
     * Disable the PDF Search Text form element.
     * 
     * There are no circumstances where editing PDF Search Text is needed since 
     * the plugin overwrites any form submitted data. Form elements with the 
     * disabled attribute will not be submitted.
     */
    public static function disablePdfSearchText($html, $inputNameStem, $value)
    {
        ob_start();
?>
<textarea name="<?php echo $inputNameStem; ?>[text]" class="textinput" rows="15" cols="50" disabled><?php echo $value; ?></textarea>
<?php
        return ob_get_clean();
    }
		
		// Get the ID of the "PDF Toc:Text" element
		public function getPdfTocElementId()
		{
			$item = new Item;
			return $item->getElementByNameAndSetName(self::ELEMENT_NAME,
																							 self::ELEMENT_SET_NAME)->id;
		}

		public function getItemRecordTypeId()
		{
			return get_db()->getTable('RecordType')->findIdFromName('item');
		}
}
