PDF TOC (plugin for Omeka)
=============================


Summary
-----------

Omeka plugin to extract TOC from PDF files, and show it on public page.

See demo of the in [1886, digital library of university Bordeaux 3 (France)](http://1886.u-bordeaux3.fr/items/show/3953).


Installation
------------
- This plugin needs pdftk command-line tool on your server

```
    sudo apt-get install pdftk
```

- Upload the PDF TOC plugin folder into your plugins folder on the server;
- you can install the plugin via github

```
    cd omeka/plugins  
    git clone git@github.com:symac/Plugin-PdfToc.git "PdfToc"
```

- Activate it from the admin → Settings → Plugins page
- Click the Configure link to process or not existing PDF files.


Using the PDF TOC Plugin
---------------------------

- Create an item
- Add PDF file(s) to this item
- Save Item
- To locate extracted table of content, select the item to which the PDF is attached. Select File from the Item navigation. Click on the name of the file. 


Optional plugins
----------------

- [BookReader](https://github.com/jsicot/BookReader) : This plugin adds Internet Archive BookReader into Omeka.

See demo of the in [Bibliothèque numérique de l'université Rennes 2 (France)](http://bibnum.univ-rennes2.fr/items/show/572).



Troubleshooting
---------------

See online [PDF TOC issues](https://github.com/symac/Plugin-PdfToc/issues).


License
-------

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Contact
-------

* Syvain Machefert, Université Bordeaux 3 (see [symac](https://github.com/symac))


Copyright
---------

The source code of [Internet Archive BookReader] is licensed under AGPL v3, as
described in the LICENSE file.

* Copyright Internet Archive, 2008-2009


