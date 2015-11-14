<?php
namespace Jackbooted\Html;

use \Jackbooted\Util\Invocation;
/**
 */

class Widget extends \Jackbooted\Util\JB {

    private static $styleComboInvocations = 0;
    public static function comboBoxJS ( $tag, $pickList ) {

        $html = '';
        if ( self::$styleComboInvocations == 0 ) {
            self::$styleComboInvocations ++;
            // .ui-button-icon-only .ui-button-text, .ui-button-icons-only .ui-button-text
            $css = <<<CSS
                .ui-button-icon-only .ui-button-text {
                    padding: .0em;
                }
                .ui-widget {
                    font-size: 1.0em;
                }
CSS;
            $html .= JS::css ( $css );
        }

        $maxLength = max( array_map( 'strlen', $pickList ) );
        if ( $maxLength > 20 ) $maxLength = 20;

        $pickListJSON = json_encode ( $pickList );
        $js = <<<JS
            var isOpen = false;
            $().ready ( function () {
                $("$tag")
                    .autocomplete({
                        minLength: 0,
                        open: function(event, ui) { isOpen = true; },
                        close: function(event, ui) { isOpen = false; },
                        source: $pickListJSON
                    })
                    .attr( 'size', $maxLength );
                $('<button type="button">&nbsp;</button>')
                    .attr( 'tabIndex', -1 )
                    .attr( 'title', 'Show All Items' )
                    .button({
                        icons: { primary: 'ui-icon-triangle-1-s' },
                        text: false
                    })
                    .removeClass( 'ui-corner-all' )
                    .addClass( 'ui-corner-right ui-button-icon' )
                    .insertAfter ( $("$tag") )
                    .click(function() {
                        if ( isOpen ) {
                            $('$tag').autocomplete('close');
                        }
                        else {
                            $('$tag').autocomplete('search','').focus();
                        }
                    });
            });
JS;
        return JS::libraryWithDependancies ( JS::JQUERY_UI ) .
               $html .
               JS::javaScript ( $js );
    }

    public static function popupWrapper ( $msg, $timeout=1500, $title='' ) {
        $id = Invocation::next();
        if ( $title == '' ) $title = 'Message:';

        if ( $timeout < 0 ) {
            $timeoutJS = <<<JS
            modal: true,
JS;
        }
        else {
            $timeoutJS = <<<JS
            modal: false,
            open: function(event, ui) {
                setTimeout ( "$('#popupWrapper_$id').dialog('close')", $timeout );
            },
JS;

        }

        $js = <<<JS
    $().ready(function() {
        $('<div id="popupWrapper_$id" title="$title">$msg</div>' )
            .dialog({
                $timeoutJS
                hide: 'fade',
                position: { at: "top+200" }
            });
    });
JS;

        return JS::libraryWithDependancies ( JS::JQUERY_UI ) .
               JS::javaScript ( $js );
    }

    private static $styleTableInvocations = 0;
    public static function styleTable ( $selector ) {
        if ( self::$styleTableInvocations == 0 ) {
            self::$styleTableInvocations ++;
            $js = <<<JS
                (function ($) {
                    $.fn.styleTable = function (options) {
                        var defaults = {
                            css: 'styleTable'
                        };
                        options = $.extend(defaults, options);

                        return this.each(function () {

                            input = $(this);
                            input.addClass(options.css);

                            input.find("tr").on('mouseover mouseout', function (event) {
                                if (event.type == 'mouseover') {
                                    $(this).children("td").addClass("ui-state-hover");
                                } else {
                                    $(this).children("td").removeClass("ui-state-hover");
                                }
                            });

                            input.find("th").addClass("ui-state-default");
                            input.find("td").addClass("ui-widget-content");

                            input.find("tr").each(function () {
                                $(this).children("td:not(:first)").addClass("first");
                                $(this).children("th:not(:first)").addClass("first");
                            });
                        });
                    };
                })(jQuery);
JS;
            $css = <<<CSS
                .styleTable { border-collapse: separate; }
                .styleTable TD { font-weight: normal !important; padding: .3em; border-top-width: 0px !important; }
                .styleTable TH { text-align: center; padding: .5em .3em; }
                .styleTable TD.first, .styleTable TH.first { border-left-width: 0px !important; }
CSS;
            $html = JS::library( JS::JQUERY_UI_CSS ) .
                    JS::css ( $css ) .
                    JS::libraryWithDependancies ( JS::JQUERY ) .
                    JS::javaScript ( $js );
        }
        else {
            $html = '';
        }
        $js = <<<JS
            $().ready(function () {
                $('$selector').styleTable();
            });
JS;
        return $html .
               JS::javaScript ( $js );
    }

    private static $datePickerJSDisplayed = false;
    public static function datePickerJS ( $selector='input.datepicker') {
        if ( self::$datePickerJSDisplayed ) return '';
        self::$datePickerJSDisplayed = true;

        $js = <<<JS
    $().ready(function() {
        $( "$selector" ).each( function() {
            $(this).datepicker({
                dateFormat: "yy-mm-dd"
            });
        });
    });
JS;
        return JS::libraryWithDependancies( JS::JQUERY_UI ) .
               JS::javaScript ( $js );
    }

    public static function reload ( $callBack, $url, $numOfSeconds=20, $css='ReloadWidget'  ) {
        $id = '_' . Invocation::next();

        $js = <<<JS
            var countDownInterval{$id} = {$numOfSeconds}; //configure refresh interval (in seconds)
            var countDownTime{$id} = countDownInterval{$id} + 1;
            var reloadTimes{$id} = 0;
            var counter{$id};

            function countDown{$id}(){
                countDownTime{$id}--;
                $('#stop{$id}').show();
                $('#start{$id}').hide();
                if ( countDownTime{$id} <= 0 ) {
                    clearTimeout(counter{$id});
                    updateReloadArea{$id}();
                    return;
                }
                $('#countDownText{$id}').html( countDownTime{$id} + '' );
                counter{$id} = setTimeout( "countDown{$id}()", 1000 );
            }

            function stopCount{$id}(){
                clearTimeout(counter{$id})
                $('#stop{$id}').hide();
                $('#start{$id}').show();
            }

            function updateReloadArea{$id}(){
                countDownTime{$id} = countDownInterval{$id} + 1;
                reloadTimes{$id} = reloadTimes{$id} + 1;
                $('#reload{$id}').load('{$url}&R='+reloadTimes{$id}, function() {
                    countDown{$id} ();
                });
            }

            $().ready ( function () {
                countDown{$id} ();
            });
JS;

        $html = 'Next '.
                Tag::hRef("javascript:countDownTime{$id}=0", 'refresh',  [ 'title' => 'Click here to refresh now.',
                                                                           'class'  => $css ] ) .
                ' in ' .
                Tag::hTag( 'span',  [ 'id'   => "countDownText{$id}",
                                           'class' => $css ] ) .
                  $numOfSeconds .
                Tag::_hTag( 'span' ) .
                ' seconds ' .
                Tag::hRef("javascript:stopCount{$id}()", 'Stop',   [ 'id' => "stop{$id}",
                                                                     'title' => 'Click here to stop the timer.',
                                                                     'class'  => $css ] ) .
                Tag::hRef("javascript:countDown{$id}()", 'Start',  [ 'id' => "start{$id}",
                                                                     'title' => 'Click here to start the timer.',
                                                                     'class'  => $css ] ) .
                '<br/>' .
                Tag::div(  [ 'id'    => "reload{$id}",
                                  'class' => $css ] ) .
                  call_user_func( $callBack ) .
                Tag::_div();

        return JS::library ( JS::JQUERY ) .
               JS::javaScript( $js ) .
               $html;
    }
}