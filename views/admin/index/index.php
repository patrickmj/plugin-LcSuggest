<?php
$head = array('bodyclass' => 'lc-suggest primary', 
              'title' => 'Library of Congress Suggest');
head($head);
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#element-id').change(function() {
        jQuery.post(
            <?php echo js_escape(uri('lc-suggest/index/suggest-endpoint')); ?>, 
            {element_id: jQuery('#element-id').val()}, 
            function(data) {
                jQuery('#suggest-endpoint').val(data);
            }
        );
    });
});
</script>
<h1><?php echo $head['title']; ?></h1>
<div id="primary">
    <?php echo flash(); ?>
    <form method="post" action="<?php echo uri('lc-suggest/index/edit-element-suggest'); ?>">
        <div class="field">
            <label for="element-id">Element</label>
            <div class="inputs">
                <?php echo $this->formSelect('element_id', 
                                             null, 
                                             array('id' => 'element-id'), 
                                             $formElementOptions) ?>
                <p class="explanation">Select an element to assign it a Library 
                of Congress authority/vocabulary. Elements already assigned an 
                authority/vocabulary are marked with an asterisk (*).</p>
            </div>
        </div>
        <div class="field">
            <label for="suggest-endpoint">Authority/Vocabulary</label>
            <div class="inputs">
                <?php echo $this->formSelect('suggest_endpoint', 
                                             null, 
                                             array('id' => 'suggest-endpoint'), 
                                             $formSuggestOptions); ?>
                <p class="explanation">Enter a Library of Congress 
                authority/vocabulary to enable the suggest (autocomplete) 
                feature for the above element. To disable the feature just 
                deselect the authority/vocabulary.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('edit-element-suggest', 
                                     'Edit Suggest', 
                                     array('class' => 'submit submit-large')); ?>
    </form>
</div>
<?php foot(); ?>