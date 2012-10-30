<?php
require_once 'PdfTocPlugin.php';
$plugin = new PdfTocPlugin;
$plugin->setUp();

$filter = array('Form', 'Item', PdfTocPlugin::ELEMENT_SET_NAME, PdfTocPlugin::ELEMENT_NAME);
add_filter($filter, 'PdfTocPlugin::forceUpdateToc');

// La fonction suivante va nous permettre d'ajouter la TDM dans le show.php d'une notice
// Dans l'idéal ça serait bien de mettre la fonction Dans PdfTocPlugin.php mais soucis pour y accéder
// pour le moment, on la laisse donc là
function PdfTocPublicShow($item = null)
{
  $localTab = $item->getElementTextsByElementNameAndSetName('Text', 'PDF Table Of Content');
  $toc = $localTab[0]["text"];

  // TODO : mieux gérer les exceptions dans les PDF sans TDM
  if (preg_match("/InfoValue/", $toc))
  {
    return;
  }

  $sortie = "";
  $tab_toc = preg_split("/\n/", $toc);
  
  $niveau_pdt = "";
  
  for ($i = 0; $i <= sizeof($tab_toc); $i++)
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
      $sortie .= "<li><a onclick='javascript:goToPage(".($page - 1).")' href='".uri()."?page=".($page - 1)."#lire-doc'>".$titre."</a></li>\n";      
    }
    $niveau_pdt = $niveau;
  }
    
  return $sortie;
}
