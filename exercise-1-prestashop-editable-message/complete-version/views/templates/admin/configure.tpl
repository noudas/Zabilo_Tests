{*
* MyModule
*
* @author    Your Name
* @copyright 2024 Your Company
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="panel">
    <h3><i class="icon-list-ul"></i> {l s='My Module Configuration' mod='mymodule'}</h3>
    
    <form id="configuration_form" class="defaultForm form-horizontal" action="{$current|escape:'html':'UTF-8'}&token={$token|escape:'html':'UTF-8'}" method="post">
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Homepage Message' mod='mymodule'}</label>
            <div class="col-lg-9">
                {foreach from=$languages item=language}
                    <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                        <div class="col-lg-9">
                            <input type="text" 
                                   name="MYMODULE_MESSAGE_{$language.id_lang}" 
                                   value="{$MYMODULE_MESSAGE_{$language.id_lang}|escape:'html':'UTF-8'}"
                                   class="form-control" 
                                   required="required" />
                        </div>
                        <div class="col-lg-2">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                    <li><a href="javascript:hideOtherLanguage({$lang.id_lang});" tabindex="-1">{$lang.name}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submit_mymodule">
                <i class="process-icon-save"></i> {l s='Save' mod='mymodule'}
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
    function hideOtherLanguage(id_lang) {
        $('.translatable-field').hide();
        $('.lang-' + id_lang).show();
    }
</script>
