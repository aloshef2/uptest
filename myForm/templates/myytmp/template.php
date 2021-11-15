<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init();
?>

<div class="bx-system-auth-form">

<?
if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
	ShowMessage($arResult['ERROR_MESSAGE']);
?>

<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
    async defer>
	</script>

<script type="text/javascript">
				BX.ready(function() {
					var loginCookie = BX.getCookie("<?=CUtil::JSEscape($arResult["~LOGIN_COOKIE_NAME"])?>");
					if (loginCookie)
					{
						var form = document.forms["system_auth_form<?=$arResult["RND"]?>"];
						var loginInput = form.elements["USER_LOGIN"];
						loginInput.value = loginCookie;
					}
					window.addEventListener("DOMContentLoaded", () => {
					const tabs = document.querySelectorAll('[role="tab"]');
					const tabList = document.querySelector('[role="tablist"]');

					tabs.forEach(tab => {
						tab.addEventListener("click", changeTabs);
					});

					let tabFocus = 0;

					tabList.addEventListener("keydown", e => {
						if (e.keyCode === 39 || e.keyCode === 37) {
						tabs[tabFocus].setAttribute("tabindex", -1);
						if (e.keyCode === 39) {
							tabFocus++;
							if (tabFocus >= tabs.length) {
							tabFocus = 0;
							}
						} else if (e.keyCode === 37) {
							tabFocus--;
							if (tabFocus <script) {
							tabFocus = tabs.length - 1;
							}
						}

						tabs[tabFocus].setAttribute("tabindex", 0);
						tabs[tabFocus].focus();
						}
					});
					});

					function changeTabs(e) {
					const target = e.target;
					const parent = target.parentNode;
					const grandparent = parent.parentNode;
					parent.querySelectorAll('[aria-selected="true"]').forEach(t => t.setAttribute("aria-selected", false));
					target.setAttribute("aria-selected", true);
					grandparent.querySelectorAll('[role="tabpanel"]').forEach(p => p.setAttribute("hidden", true));
					grandparent.parentNode.querySelector(`#${target.getAttribute("aria-controls")}`).removeAttribute("hidden");
					}
					window.onload = (event) => {
					function onloadCallback(){
						var btnSubmit = document.getElementById('btnSubmit');
						btnSubmit.removeAttribute('disabled');
						}
					};
				});
			</script>
<?if($arResult["FORM_TYPE"] == "login"):?>

<form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?if($arResult["BACKURL"] <> ''):?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?endif?>
<?foreach ($arResult["POST"] as $key => $value):?>
	<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
<?endforeach?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="AUTH" />
	<table width="95%">
		<tr>
			<td colspan="2">
			
			
			<div class="tabs">
				<div role="tablist" aria-label="Sample Tabs">
					<span role="tab" aria-selected="true" aria-controls="panel-1" id="tab-1" tabindex="0">
						По логину
						</span>
					<span role="tab" aria-selected="false" aria-controls="panel-2" id="tab-2" tabindex="1">
						По телефону
						</span>
				</div>
				<div id="panel-1" role="tabpanel" tabindex="0" aria-labelledby="tab-1">
					<span name="user" class="span">Логин</span>:<br />
					<input type="text" name="USER_LOGIN" id="USER_LOGIN" maxlength="50" value="" size="17" /><br/>
				</div>
				<div id="panel-2" role="tabpanel" tabindex="1" aria-labelledby="tab-2" hidden>
					<span name="user" class="span">Телефон</span>:<br />
					<input type="phone" name="USER_PHONE" id="USER_PHONE" maxlength="50" value="" size="17" />
				</div>
			</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<?=GetMessage("AUTH_PASSWORD")?>:<br />
			<input type="password" name="USER_PASSWORD" maxlength="255" size="17" autocomplete="off" />
			
			<?if($arResult["SECURE_AUTH"]):?>
				<span class="bx-auth-secure" id="bx_auth_secure<?=$arResult["RND"]?>" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
				<noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
				</noscript>
				<script type="text/javascript">
				document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
				</script>

			<?endif?>
			</td>
		</tr>

		<?if($arResult["googleCaptcha"] == true):?>
			<tr>
			<td colspan="2"><input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" /></td>
		</tr>
		<?else:?>
			<tr>
			<td colspan="2">
			<form id='sendForm' action="?" method="POST">
			<div class="g-recaptcha" data-sitekey="тут открытый ключ" data-callback='onloadCallback'></div>
			<br/>
			<input type="submit" name="Login" value="Submit" id="btnSubmit">
			</form>
		</td>
		</tr>
		<?endif?>
<?if($arResult["NEW_USER_REGISTRATION"] == "Y"):?>
		<tr>
			<td colspan="2"><noindex><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REGISTER")?></a></noindex><br /></td>
		</tr>
<?endif?>

		<tr>
			<td colspan="2"><noindex><a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></noindex></td>
		</tr>
	</table>
</form>

<?if($arResult["AUTH_SERVICES"]):?>
<?
$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "", 
	array(
		"AUTH_SERVICES"=>$arResult["AUTH_SERVICES"],
		"AUTH_URL"=>$arResult["AUTH_URL"],
		"POST"=>$arResult["POST"],
		"POPUP"=>"Y",
		"SUFFIX"=>"form",
	), 
	$component, 
	array("HIDE_ICONS"=>"Y")
);
?>
<?endif?>

<?
elseif($arResult["FORM_TYPE"] == "otp"):
?>

<form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?if($arResult["BACKURL"] <> ''):?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?endif?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="OTP" />
	<table width="95%">
		<tr>
			<td colspan="2">
			<?echo GetMessage("auth_form_comp_otp")?><br />
			<input type="text" name="USER_OTP" maxlength="50" value="" size="17" autocomplete="off" /></td>
		</tr>
<?if ($arResult["CAPTCHA_CODE"]):?>
	
		<tr>
			<td colspan="2">
			<?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:<br />
			<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /><br /><br />
			<input type="text" name="captcha_word" maxlength="50" value="" /></td>
		</tr>
<?endif?>
<?if ($arResult["REMEMBER_OTP"] == "Y"):?>
		<tr>
			<td valign="top"><input type="checkbox" id="OTP_REMEMBER_frm" name="OTP_REMEMBER" value="Y" /></td>
			<td width="100%"><label for="OTP_REMEMBER_frm" title="<?echo GetMessage("auth_form_comp_otp_remember_title")?>"><?echo GetMessage("auth_form_comp_otp_remember")?></label></td>
		</tr>
<?endif?>
		<tr>
			<td colspan="2"><input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" /></td>
		</tr>
		<tr>
			<td colspan="2"><noindex><a href="<?=$arResult["AUTH_LOGIN_URL"]?>" rel="nofollow"><?echo GetMessage("auth_form_comp_auth")?></a></noindex><br /></td>
		</tr>
	</table>
</form>

<?
else:
?>

<form action="<?=$arResult["AUTH_URL"]?>">
	<table width="95%">
		<tr>
			<td align="center">
				<?=$arResult["USER_NAME"]?><br />
				[<?=$arResult["USER_LOGIN"]?>]<br />
				<a href="<?=$arResult["PROFILE_URL"]?>" title="<?=GetMessage("AUTH_PROFILE")?>"><?=GetMessage("AUTH_PROFILE")?></a><br />
			</td>
		</tr>
		<tr>
			<td align="center">
			<?foreach ($arResult["GET"] as $key => $value):?>
				<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
			<?endforeach?>
			<?=bitrix_sessid_post()?>
			<input type="hidden" name="logout" value="yes" />
			<input type="submit" name="logout_butt" value="<?=GetMessage("AUTH_LOGOUT_BUTTON")?>" />
			</td>
		</tr>
	</table>
</form>
<?endif?>
</div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

