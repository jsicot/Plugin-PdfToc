<div class="field">
    <div id="pdf_toc_process_label" class="two columns alpha">
        <label for="pdf_toc_process"><?php echo __('Process existing PDF files'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
        <?php 
        echo __(
            'This plugin extracting table of contents/bookmarks from PDF files.'
            . 'This normally happens automatically, but there are times when '
            . 'you\'ll want to extract text from all PDF files that '
            . 'already exist in your Omeka repository, like when first '
            . 'installing this plugin. Check the box below and submit '
            . 'this form to run the text extraction process, which may '
            . 'take some time to finish.');
        ?>
        </p>
        <?php if ($this->valid_storage_adapter): ?>
        <?php echo $this->formCheckbox('pdf_toc_process'); ?>
        <?php else: ?>
        <p class="error">
        <?php
        echo __(
            'This plugin does not support processing of PDF files that '
            . 'are stored remotely. Processing existing PDF files has been '
            . 'disabled.'
        );
        ?>
        </p>
        <?php endif; ?>
    </div>
</div>
