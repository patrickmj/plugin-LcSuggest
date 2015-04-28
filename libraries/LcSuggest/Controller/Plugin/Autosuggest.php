<?php
/**
 * Library of Congress Suggest
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Library of Congress Suggest controller plugin.
 * 
 * @package Omeka\Plugins\LcSuggest
 */
class LcSuggest_Controller_Plugin_Autosuggest extends Zend_Controller_Plugin_Abstract
{
    /**
     * Add autosuggest only during defined routes.
     */
    public function preDispatch($request)
    {
        $db = get_db();
        
        // Set NULL modules to default. Some routes do not have a default 
        // module, which resolves to NULL.
        $module = $request->getModuleName();
        if (is_null($module)) {
            $module = 'default';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        // Include all routes (route + controller + actions) that render an 
        // element form, including actions requested via AJAX.
        $routes = array(
            array('module' => 'default', 
                  'controller' => 'items', 
                  'actions' => array('add', 'edit', 'element-form', 'change-type'))
        );
        
        // Allow plugins to add routes that contain form inputs rendered by 
        // Omeka_View_Helper_ElementForm::_displayFormInput().
        $routes = apply_filters('lc_suggest_routes', $routes);
        
        // Add the autosuggest JavaScript to the JS queue.
        $view = Zend_Registry::get('view');
        $view->headLink()->appendStylesheet('/plugins/LcSuggest/views/javascripts/treemenu/jquery.treeview.css');
        $view->headScript()->appendFile('/plugins/LcSuggest/views/javascripts/treemenu/jquery.treeview.min.js', $type='text/javascript');
        
        // Iterate the defined routes.
        foreach ($routes as $route) {
            
            // Set the autosuggest if the current action matches a defined route.
            if ($route['module'] === $module && $route['controller'] === $controller 
                && in_array($action, $route['actions'])) {
                
                $lcTable = $db->getTable('LcSuggest');
                $endpoints = $lcTable->getSuggestEndpoints();
                
                // Iterate the elements that are assigned to a suggest endpoint.
                $lcSuggests = $lcTable->findAll();
                
                foreach ($lcSuggests as $lcSuggest) {
                    
                    $element = $db->getTable('Element')->find($lcSuggest->element_id);
                    $elementSet = $db->getTable('ElementSet')->find($element->element_set_id);
                    
                    // retrieve URI for element and test to see whether we need to render for multi (combo) lookups
                    $endpoint = $lcTable->findByElementId($lcSuggest->element_id)->suggest_endpoint;

                    // Add the autosuggest JavaScript to the JS queue.
                    $view = Zend_Registry::get('view');
                    $view->headScript()->captureStart();

                    if (!empty($endpoints[$endpoint]['multi']))
                    {
?>
    function setElementContents(element_id, sub_id, label, value)
    {
        // set contents of field
        jQuery("#Elements-" + element_id + "-" + sub_id + "-text").val(label);
        
        // set URI contents if available
        if (jQuery("#Elements-" + element_id + "-" + sub_id + "-uri")[0] != undefined)
        {
            // set contents of field
            jQuery("#Elements-" + element_id + "-" + sub_id + "-uri").val(value);
        }
        
        multidiv = jQuery("#Elements-" + element_id + "-" + sub_id + "-multidiv");
        multidiv.hide();
    }

    //  subclass our tree widget so it hides its native menu (we build our own)
    jQuery.widget( "ui.autocompletetree", jQuery.ui.autocomplete, {
    
       _renderMenu: function( ul, items ) {
            
          }
    });

    // Add autosuggest to <?php echo $elementSet->name . ':' . $element->name; ?>. Used by the LC Suggest plugin.
    jQuery(document).bind('omeka:elementformload', function(event) {
        jQuery('#element-<?php echo $element->id; ?> textarea').autocompletetree({
            minLength: 2,
            source: <?php echo json_encode($view->url('lc-suggest/index/suggest-endpoint-proxy/element-id/' . $element->id)); ?>,
            select: function( event, ui ) {
                if( ui.item && ui.item.value)
                {
                    sub_id = event.target.id.split('-')[2]  // determine sub id for multiple fields of same type
                
                    //  test for URI input available (from LinkedDataElements plugin)
                    if (jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-uri")[0] != undefined)
                    {
                        jQuery("textarea#Elements-<?php echo $element->id; ?>-" + sub_id + "-text").val(ui.item.label);
                        
                        //  distinguish between array of values vs key value pairs
                        if (ui.item.value != ui.item.label)
                        {
                            jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-uri").val(ui.item.value);
                        }
                        event.preventDefault();
                    }
                    else
                    {
                        if (ui.item.label)
                            jQuery("textarea#Elements-<?php echo $element->id; ?>-" + sub_id + "-text").val(ui.item.label);
                        event.preventDefault();
                    }
                }
            },
            open: function(event, ui) {
                event.preventDefault();
            },
            response: function( event, ui )
            {
                sub_id = event.target.id.split('-')[2]  // determine sub id for multiple fields of same type
                multidiv = jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-multidiv");
                multidiv.html("<ul id='Elements-<?php echo $element->id; ?>-" + sub_id + "-multilookup' class='treemenu'></ul>");
                multilookup = jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-multilookup");
                multidiv.show();
                multilookup.treeview({
                    collapsed: false,
                    unique: false,
                    persist: "location",
                });

//                console.log(ui.content);
                list_html = '';
                jQuery.each( ui.content, function( index, item )
                {
                    if (typeof item['sourceTitle'] !== 'undefined')
                    {
                        list_html += '<li><b>' + item['sourceTitle'] + '</b>';
                        list_html += '<ul>';
                        jQuery.each( item, function( index, listitem )
                        {
                            if (listitem != item['sourceTitle'])
                            {
                                list_html += '<li value="' + listitem.value + '">';
                                list_html += '<a href="javascript:setElementContents(<?php echo $element->id; ?>, sub_id, \'' + listitem.label + '\', \'' + listitem.value + '\');">' + listitem.label + '</a></li>';
                            }
                        });
                        list_html += '</ul></li>';
                    }
                })
                
                sub_id = event.target.id.split('-')[2]  // determine sub id for multiple fields of same type
                
                var menutree = jQuery(
                    list_html
                ).appendTo("#Elements-<?php echo $element->id; ?>-" + sub_id + "-multilookup");
                jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-multilookup").treeview({
                    add: menutree
                });
            }
        })
    });
    
<?php
                    }
                    else
                    {
?>
    // Add autosuggest to <?php echo $elementSet->name . ':' . $element->name; ?>. Used by the LC Suggest plugin.
    jQuery(document).bind('omeka:elementformload', function(event) {
        jQuery('#element-<?php echo $element->id; ?> textarea').autocomplete({
            minLength: 2,
            source: <?php echo json_encode($view->url('lc-suggest/index/suggest-endpoint-proxy/element-id/' . $element->id)); ?>,
            select: function( event, ui ) {
                if( ui.item )
                {
                    sub_id = event.target.id.split('-')[2]  // determine sub id for multiple fields of same type
                
                    //  test for URI input available (from LinkedDataElements plugin)
                    if (jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-uri")[0] != undefined)
                    {
                        jQuery("textarea#Elements-<?php echo $element->id; ?>-" + sub_id + "-text").val(ui.item.label);
                        
                        //  distinguish between array of values vs key value pairs
                        if (ui.item.value != ui.item.label)
                        {
                            jQuery("#Elements-<?php echo $element->id; ?>-" + sub_id + "-uri").val(ui.item.value);
                        }
                        event.preventDefault();
                    }
                    else
                    {
                        jQuery("textarea#Elements-<?php echo $element->id; ?>-" + sub_id + "-text").val(ui.item.label);
                        event.preventDefault();
                    }
                }
            }

        });
    });
<?php
                    }
                    
                    $view->headScript()->captureEnd();
                }
                
                // Once the JavaScript is applied there is no need to continue 
                // looping the defined routes.
                break;
            }
        }
    }
}
