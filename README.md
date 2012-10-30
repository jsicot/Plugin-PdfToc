Plugin-PdfToc
=============

Omeka plugin to extract TOC from PDF files, and show it on public page. This plugin needs pdftk to extract OCR.

Usage
=====
After installation, this plugin can be used by calling the function PdfTocPublicShow() which returns the HTML of the TOC, with links with anchor : items/show/[ID]?page=XX#lire-doc . This link can be modified in plugin.php

License
=======
This plugin is based on PDF Search plugin developped by CHNM ( http://omeka.org/add-ons/plugins/pdf-search/ )
