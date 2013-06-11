<?php
/**
 * PDF ToC
 *
 * Adapted from PDF Text plugin by Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The PDF Text plugin.
 * 
 * @package Omeka\Plugins\PdfToc
 */
class PdfTocPlugin extends Omeka_Plugin_AbstractPlugin
{
    const ELEMENT_SET_NAME = 'PDF Table of Contents';
    const ELEMENT_NAME = 'Text';

    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'before_save_file',
        'toc_for_bookreader',
    );

    protected $_pdfMimeTypes = array(
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'text/x-pdf',
        'text/pdf',
        'applications/vnd.pdf',
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Don't install if the pdftk command doesn't exist.
        // See: http://stackoverflow.com/questions/592620/check-if-a-program-exists-from-a-bash-script
        if ((int) shell_exec('hash pdftk 2>&- || echo -1')) {
            throw new Omeka_Plugin_Installer_Exception(__('The pdftk command-line utility ' 
            . 'is not installed. pdftk must be installed to install this plugin.'));
        }
        // Don't install if a PDF element set already exists.
        if ($this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)) {
            throw new Omeka_Plugin_Installer_Exception(__('An element set by the name "%s" already ' 
            . 'exists. You must delete that element set to install this plugin.', self::ELEMENT_SET_NAME));
        }
        insert_element_set(
            array('name' => self::ELEMENT_SET_NAME, 'record_type' => 'File'),
            array(array('name' => self::ELEMENT_NAME))
        );
    }

    /**
     * Uninstall the plugin
     */
    public function hookUninstall()
    {
        // Delete the PDF element set.
        $this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)->delete();
    }


    /**
     * Display the config form.
     */
    public function hookConfigForm()
    {
        echo get_view()->partial(
            'plugins/pdf-toc-config-form.php', 
            array('valid_storage_adapter' => $this->isValidStorageAdapter())
        );
    }

    /**
     * Handle the config form.
     */
    public function hookConfig()
    {
        // Run the text extraction process if directed to do so.
        if ($_POST['pdf_toc_process'] && $this->isValidStorageAdapter()) {
            Zend_Registry::get('bootstrap')->getResource('jobs')
                ->sendLongRunning('PdfTocProcess');
        }
    }

    /**
     * Add the PDF text to the file record.
     * 
     * This has a secondary effect of including the text in the search index.
     */
    public function hookBeforeSaveFile($args)
    {
        // Extract table of contents only on file insert.
        if (!$args['insert']) {
            return;
        }
        $file = $args['record'];
        // Ignore non-PDF files.
        if (!in_array($file->mime_type, $this->_pdfMimeTypes)) {
            return;
        }
        // Add the PDF toc to the file record.
        $element = $file->getElement(self::ELEMENT_SET_NAME, self::ELEMENT_NAME);
        $dump_data = $this->pdfToc($file->getPath());
        // pdftoc must return a string to be saved to the element_texts table.
        if (is_string($dump_data)) {
        
        //preprare ToC
            $dump_data = preg_replace("/^.*(Bookmark.*)$/isU", "$1", $dump_data);
            $dump_data_array = preg_split("/\n/", $dump_data);
            $dump_data_array = preg_split("/\n/", $dump_data);
            
            $toc = "";
            for ($i = 0; $i <= sizeof($dump_data_array); $i+=3)
            {
                $bm_title = str_replace("BookmarkTitle: ", "", $dump_data_array[$i]);
                $bm_level = str_replace("BookmarkLevel: ", "", $dump_data_array[$i+1]);
                $bm_page = str_replace("BookmarkPageNumber: ", "", $dump_data_array[$i+2]);
                if ($toc != "")
                {
                    $toc .= "\n";
                }
                if ( ($bm_level != "") and ($bm_title != "") and ($bm_page != "") )
                {
                    $toc .= $bm_level."|".$bm_title."|".$bm_page;
                }
            }
            $file->addTextForElement($element, $toc);
        }
    }

    /**
     * Extract the table of contents from a PDF file.
     * 
     * @param string $path
     * @return string
     */
    public function pdfToc($path)
    {
        $path = escapeshellarg($path);
        return shell_exec("pdftk $path dump_data");
    }

    /**
     * Determine if the plugin supports the storage adapter.
     * 
     * 
     * @return bool
     */
    public function isValidStorageAdapter()
    {
        $storageAdapter = Zend_Registry::get('bootstrap')
            ->getResource('storage')->getAdapter();
        if (!($storageAdapter instanceof Omeka_Storage_Adapter_Filesystem)) {
            return false;
        }
        return true;
    }

    /**
     * Get the PDF MIME types.
     * 
     * @return array
     */
    public function getPdfMimeTypes()
    {
        return $this->_pdfMimeTypes;
    }
	
    /**
     * Display viewer.
     *
     * @param array $args
     *   Two specific arguments:
     *   - (integer) page: set the page to be shown when including the iframe,
     *   - (boolean) embed_functions: allow user to include an iframe with all
     *   functions (Zoom, Search...). Can be used to include a better viewer
     *   into items/views.php without requiring user to use the full viewer.
     *
     * @return void
     */
    public function hookTocForBookreader($args)
    {
			$view = $args['view'];
	        $item = isset($args['item']) && !empty($args['item'])
	            ? $args['item']
	            : $view->item;
			$files = $item->getFiles();
			
			foreach ($files as $file) {
				if (in_array($file->mime_type, $this->_pdfMimeTypes)) {
					$textElement = $file->getElementTexts(
	                    self::ELEMENT_SET_NAME,
	                    self::ELEMENT_NAME
	                );
  				$toc = $textElement[0];
				if (preg_match("/InfoValue/", $toc))
				  {
				    return;
				  }
				$sortie = "";
				$toc = rtrim($toc);
				$tab_toc = preg_split("/\n/", $toc);
				$niveau_pdt = "";
				$total = (count($tab_toc)-1);
				for ($i = 0; $i <= $total; $i++)
			  	{
				
			    $tab_ligne = preg_split("/\|/", $tab_toc[$i]);
			
				$niveau = $tab_ligne[0];
			    $titre  = $tab_ligne[1];
			    $page   = $tab_ligne[2];
			    if ($niveau_pdt == "")
			    {
			      // Première ligne
			      $sortie .= "<ul>";
			    }
			    elseif ($niveau_pdt < $niveau)
			    {
			      for ($k = $niveau_pdt; $k < $niveau; $k++)
			      {
			        $sortie .= "<ul>\n";
			      }
			    }
			    elseif ($niveau_pdt > $niveau)
			    {
			      for ($k = $niveau_pdt; $k > $niveau; $k--)
			      {
			        $sortie .= "</ul>\n";
			      }
			    }

			    if ($page)
			    {
			      $sortie .= "<li><a onclick='javascript:br.jumpToIndex(".($page - 1).");return false;' href='".url()."?page=".($page - 1)."#lire-doc'>".$titre."</a></li>\n";      
			    }
			    $niveau_pdt = $niveau;
			  }

			  $brToc = $sortie ;

			  include_once 'views' . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR . 'bookreader_toc.php';
					
				}	
			}
  
//end function
 }	
    
}
