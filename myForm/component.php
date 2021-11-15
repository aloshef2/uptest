<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Security\Mfa;
/*
Authorization form (for prolog)
Params:
	REGISTER_URL => path to page with authorization script (component?)
	PROFILE_URL => path to page with profile component
*/



$arParamsToDelete = array(
	"login",
	"login_form",
	"logout",
	"register",
	"forgot_password",
	"change_password",
	"confirm_registration",
	"confirm_code",
	"confirm_user_id",
	"logout_butt",
	"auth_service_id",
);
$currentUrl = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

print_r($_POST);

$arResult["BACKURL"] = $currentUrl;
$arResult["ID"] = intval($USER->GetID());
$arResult['ERROR'] = false;
$arResult['SHOW_ERRORS'] = (array_key_exists('SHOW_ERRORS', $arParams) && $arParams['SHOW_ERRORS'] == 'Y'? 'Y' : 'N');
$arResult["RND"] = $this->randString();

if(!$USER->IsAuthorized())
{
	$arResult["STORE_PASSWORD"] = COption::GetOptionString("main", "store_password", "Y") == "Y" ? "Y" : "N";
	$arResult["NEW_USER_REGISTRATION"] = COption::GetOptionString("main", "new_user_registration", "N") == "Y" ? "Y" : "N";
	if(defined("AUTH_404"))
		$arResult["AUTH_URL"] = htmlspecialcharsback(POST_FORM_ACTION_URI);
	else
		$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", array_merge($arParamsToDelete, array("logout_butt", "backurl")));

	$arParams["REGISTER_URL"] = ($arParams["REGISTER_URL"] <> ''? $arParams["REGISTER_URL"] : $currentUrl);
	$arParams["FORGOT_PASSWORD_URL"] = ($arParams["FORGOT_PASSWORD_URL"] <> ''? $arParams["FORGOT_PASSWORD_URL"] : $arParams["REGISTER_URL"]);

	$url = urlencode($APPLICATION->GetCurPageParam("", array_merge($arParamsToDelete, array("backurl"))));

	$custom_reg_page = COption::GetOptionString('main', 'custom_register_page');
	$arResult["AUTH_REGISTER_URL"] = ($custom_reg_page <> ''? $custom_reg_page : $arParams["REGISTER_URL"].(mb_strpos($arParams["REGISTER_URL"], "?") !== false? "&" : "?")."register=yes&backurl=".$url);
	$arResult["AUTH_FORGOT_PASSWORD_URL"] = $arParams["FORGOT_PASSWORD_URL"].(mb_strpos($arParams["FORGOT_PASSWORD_URL"], "?") !== false? "&" : "?")."forgot_password=yes&backurl=".$url;
	$arResult["AUTH_LOGIN_URL"] = $APPLICATION->GetCurPageParam("login_form=yes", $arParamsToDelete);

	$arRes = array();
	
	foreach($arResult as $key=>$value)
	{
		$arRes[$key] = htmlspecialcharsbx($value);
		$arRes['~'.$key] = $value;
	}
	$arResult = $arRes;



	if(CModule::IncludeModule("security") && Mfa\Otp::isOtpRequired() && $_REQUEST["login_form"] <> "yes")
	{
		
		$arResult["FORM_TYPE"] = "otp";

		$arResult["REMEMBER_OTP"] = (COption::GetOptionString('security', 'otp_allow_remember') === 'Y');

		$arResult["CAPTCHA_CODE"] = false;
		if(Mfa\Otp::isCaptchaRequired())
		{
			$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
		}
		if(Mfa\Otp::isOtpRequiredByMandatory())
		{
			$arResult['ERROR_MESSAGE'] = array("MESSAGE" => GetMessage("system_auth_form_otp_required"), "TYPE" => "ERROR");
		}
	}
	else
	{
		$arResult["FORM_TYPE"] = "login";

		$arVarExcl = array("USER_LOGIN"=>1, "USER_PASSWORD"=>1, "backurl"=>1, "auth_service_id"=>1, "TYPE"=>1, "AUTH_FORM"=>1, "PHONE_NUMBER"=>1);

				$arResult["GET"] = array();
				$arResult["POST"] = array();

		if(array_key_exists('g-recaptcha-response',$_POST)){
			$secrkey = "тут закрытый ключ";
			$ip = $_SERVER["REMOTE_ADDR"];
			$response = $_POST['g-recaptcha-response'];
			$url = "https://www.google.com/recaptcha/api/siteverify?secret=$seckey&response=$response&remoteip=$ip";
			$fire = file_get_contents($url);
			$data = json_decode($fire);

			if(!isset($_POST['g-recaptcha-response'])){
				$arResult["googleCaptcha"] = true;

				

				foreach($_POST as $vname=>$vvalue)
				{
					if(!isset($arVarExcl[$vname]))
					{
						if(!is_array($vvalue))
						{
							$arResult["POST"][htmlspecialcharsbx($vname)] = htmlspecialcharsbx($vvalue);
						}
						else
						{
							foreach($vvalue as $k1 => $v1)
							{
								if(is_array($v1))
								{
									foreach($v1 as $k2 => $v2)
									{
										if(!is_array($v2))
											$arResult["POST"][htmlspecialcharsbx($vname)."[".htmlspecialcharsbx($k1)."][".htmlspecialcharsbx($k2)."]"] = htmlspecialcharsbx($v2);
									}
								}
								else
								{
									$arResult["POST"][htmlspecialcharsbx($vname)."[".htmlspecialcharsbx($k1)."]"] = htmlspecialcharsbx($v1);
								}
							}
						}
					}
				}
		
		
		
				$loginCookieName = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN";
				$arResult["~LOGIN_COOKIE_NAME"] = $loginCookieName;
				$arResult["~USER_LOGIN"] = $_COOKIE[$loginCookieName];
				$arResult["USER_LOGIN"] = $arResult["LAST_LOGIN"] = htmlspecialcharsbx($arResult["~USER_LOGIN"]);
				$arResult["~LAST_LOGIN"] = $arResult["~USER_LOGIN"];
				$arResult["~LAST_PHONE_NUMBER"] = $arResult["PHONE_NUMBER"];
				$arResult["AUTH_SERVICES"] = false;
				$arResult["CURRENT_SERVICE"] = false;
				
				if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
				{
					$sec = new CRsaSecurity();
					if(($arKeys = $sec->LoadKeys()))
					{
						$sec->SetKeys($arKeys);
						$sec->AddToForm('system_auth_form'.$arResult["RND"], array('USER_PASSWORD'));
						$arResult["SECURE_AUTH"] = true;
					}
				}
				if($_POST['USER_PHONE']){
					$cUserPhone = $USER::GetList(
						$by="id",
						$order="desc",
						[
							'PERSONAL_PHONE' => $_POST['USER_PHONE']
						],
						[
							'SELECT' => [
								'ID'
							]
						]
					)->fetch();
					print_r($cUserPhone);
					$USER->Login($cUserPhone["LOGIN"],$_POST["USER_PASSWORD"],"Y");
					$loginCookieName = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN";
					$arResult["~LAST_PHONE_NUMBER"] = $cUserPhone["PERSONAL_PHONE"];
					$arResult["AUTH_SERVICES"] = false;
					$arResult["CURRENT_SERVICE"] = false;
					print_r($_POST);
					header("Refresh: 0");
				}

			}else{
				$arResult["googleCaptcha"] = false;
			}

			

		}else{
			$arResult['ERROR'] = true;
			$arResult["googleCaptcha"] = false;
		}

		


	}
}
else
{
	$arResult["FORM_TYPE"] = "logout";

	$arResult["AUTH_URL"] = $currentUrl;
	$arResult["PROFILE_URL"] = $arParams["PROFILE_URL"].(mb_strpos($arParams["PROFILE_URL"], "?") !== false? "&" : "?")."backurl=".urlencode($currentUrl);

	$arRes = array();
	foreach($arResult as $key=>$value)
	{
		$arRes[$key] = htmlspecialcharsbx($value);
		$arRes['~'.$key] = $value;
	}
	$arResult = $arRes;

	$arResult["USER_NAME"] = htmlspecialcharsEx($USER->GetFormattedName(false, false));
	$arResult["USER_LOGIN"] = htmlspecialcharsEx($USER->GetLogin());

	$arResult["POST"] = array();
	$arResult["GET"] = array();
	foreach($_GET as $vname=>$vvalue)
		if(!is_array($vvalue) && $vname!="backurl" && $vname != "login" && $vname != "auth_service_id")
			$arResult["GET"][htmlspecialcharsbx($vname)] = htmlspecialcharsbx($vvalue);
}

$this->IncludeComponentTemplate();