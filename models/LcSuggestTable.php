<?php
class LcSuggestTable extends Omeka_Db_Table
{
    /**
     * List of suggest endpoints made available by the Library of Congress 
     * Authorities and Vocabularies service.
     * 
     * The keys are URLs to the authority/vocabulary suggest endpoints. The 
     * values are arrays containing the authority/vocabulary name and the URL to 
     * the authority/vocabulary description page.
     * 
     * These authorities and vocabularies have been selected due to their large 
     * size and suitability to the autocomplete feature. Vocabularies not 
     * included here may be better suited as a full list controlled vocabulary.
     * 
     * @see http://id.loc.gov/
     */
    protected $_suggestEndpoints = array(
        'http://id.loc.gov/authorities/subjects/suggest' => array(
            'name' => 'Library of Congress Subject Headings', 
            'url'  => 'http://id.loc.gov/authorities/subjects.html'
        ), 
        'http://id.loc.gov/authorities/names/suggest' => array(
            'name' => 'Library of Congress Names', 
            'url'  => 'http://id.loc.gov/authorities/names.html', 
        ), 
        'http://id.loc.gov/authorities/childrensSubjects/suggest' => array(
            'name' => 'Library of Congress Children\'s Subject Headings', 
            'url'  => 'http://id.loc.gov/authorities/childrensSubjects.html', 
        ), 
        'http://id.loc.gov/authorities/genreForms/suggest' => array(
            'name' => 'Library of Congress Genre Form Headings', 
            'url'  => 'http://id.loc.gov/authorities/genreForms.html', 
        ), 
        'http://id.loc.gov/vocabulary/graphicMaterials/suggest' => array(
            'name' => 'Thesaurus for Graphic Materials', 
            'url'  => 'http://id.loc.gov/vocabulary/graphicMaterials.html', 
        ), 
        'http://id.loc.gov/vocabulary/relators/suggest' => array(
            'name' => 'MARC Code List for Relators', 
            'url'  => 'http://id.loc.gov/vocabulary/relators.html', 
        ), 
        'http://id.loc.gov/vocabulary/countries/suggest' => array(
            'name' => 'MARC List for Countries', 
            'url'  => 'http://id.loc.gov/vocabulary/countries.html', 
        ), 
        'http://id.loc.gov/vocabulary/geographicAreas/suggest' => array(
            'name' => 'MARC List for Geographic Areas', 
            'url'  => 'http://id.loc.gov/vocabulary/geographicAreas.html', 
        ), 
        'http://id.loc.gov/vocabulary/languages/suggest' => array(
            'name' => 'MARC List for Languages', 
            'url'  => 'http://id.loc.gov/vocabulary/languages.html', 
        ), 
    );
    
    public function findByElementId($elementId)
    {
        $select = $this->getSelect()->where('element_id = ?', $elementId);
        return $this->fetchObject($select);
    }
    
    public function getSuggestEndpoints()
    {
        return $this->_suggestEndpoints;
    }
}