<?php
 /**
 * WhispyForum language file - english.php
 * 
 * English localization
 * 
 * WhispyForum
 */

global $wf_lang; // Language global array

/* English localization */
$wf_lang = array(
	/* General */
	'{LANG_HELLO_EXCLAMATION}'	=>	"Hello!",
	'{LANG_WELCOME_COMMA}'	=>	"Welcome, ",
	'{LANG_ERROR_EXCLAMATION}'	=>	"Error!",
	'{LANG_ERROR_LOWERCASE_EXCLAMATION}'	=>	"error!",
	'{LANG_SUCCESS_EXCLAMATION}'	=>	"Success!",
	'{LANG_PLEASE_CONFIRM_EXCLAMATION}'	=>	"Please confirm!",
	'{LANG_LEFT}'	=>	"Left",
	'{LANG_RIGHT}'	=>	"Right",
	'{LANG_YES}'	=>	"Yes",
	'{LANG_NO}'	=>	"No",
	'{LANG_USING_LOWERCASE}'	=>	"using",
	'{LANG_USERNAME}'	=>	"Username",
	'{LANG_USERNAME_LOWERCASE}'	=>	"username",
	'{LANG_PASSWORD}'	=>	"Password",
	'{LANG_PASSWORD_LOWERCASE}'	=>	"password",
	'{LANG_PASSWORD_AGAIN}'	=>	"Password (again)",
	'{LANG_EMAIL}'	=>	"E-mail",
	'{LANG_PROCEED_CLICK_BUTTON_BELOW}'	=>	"To proceed, please click the button below.",
	'{LANG_BB_BACK}'	=>	"<< Back",
	'{LANG_NEXT_NN}'	=>	"Next >>",
	'{LANG_HOMEPAGE_NN}'	=>	"Homepage >>",
	'{LANG_HOMEPAGE}'	=>	"homepage",
	'{LANG_TO_RETURN_HOME_CLICK_BUTTON_BELOW}'	=>	"To return to the homepage, please click the button below.",
	'{LANG_TO_RETRY_CLICK_BACK}'	=>	"If you want to retry, click back.",
	'{LANG_CAN_PROCEED_TO_NEXT_STEP}'	=>	"You can proceed to the next step.",
	'{LANG_REDIRECT_WHERE_LEFTOFF}'	=>	"You're being redirected where you left off...",
	'{LANG_REDIRECT_THERE}'	=>	"You're being redirected there...",
	'{LANG_REDIRECT_MAIN_PAGE}'	=>	"You're being redirected to the main page...",
	'{LANG_REDIRECT_RETURN_LINK}'	=>	"Click here if your browser does not redirect you or if don't want to wait...",
	'{LANG_MANDATORY_VARIABLES_REDSTAR}'	=>	'Variables marked with a red star (<span class="red-star">*</span>) are required to be entered.',
	'{LANG_IS_MANDATORY}'	=>	"is mandatory",
	'{LANG_YOU_NOT_ENTERED}'	=>	"You did not enter",
	'{LANG_DIRECT_OPENING}'	=>	"Direct opening",
	'{LANG_GOBACKTOTHE}'	=>	"Go back to the",
	'{LANG_GOTOTHE}'	=>	"Go to the",
	'{LANG_FILE}'	=>	"file",
	'{LANG_TEMPLATE}'	=>	"Template",
	'{LANG_DOES_NOT_EXIST}'	=>	"does not exist",
	'{LANG_FILE_UNAVIABLE}'	=>	"File unaviable",
	'{LANG_COULD_NOT_BE_MADE}'	=>	"could not be made.",
	'{LANG_UPLOAD}'	=>	"Upload",
	'{LANG_OR}'	=>	"or",
	'{LANG_PIXELS}'	=>	"pixels",
	'{LANG_PLEASE_TRY_AGAIN}'	=>	"Please try again.",
	'{LANG_UPLOADED_SUCCESSFULLY}'	=>	"Uploaded successfully!",
	'{LANG_ACTIONS}'	=>	"Actions",
	'{LANG_EDIT}'	=>	"Edit",
	'{LANG_DELETE}'	=>	"Delete",
	'{LANG_MISSING_PARAMETERS}'	=>	"Missing parameters",
	'{LANG_MISSING_PARAMETERS_BODY}'	=>	"One or more of the required parameters hadn't been passed.",
	
	/* SQL */
	'{LANG_SQL_ERROR}'	=>	"mySQL error",
	'{LANG_SQL_EXEC_ERROR}'	=>	"Query execution error",
	'{LANG_SQL_THEQUERY}'	=>	"The specified query",
	'{LANG_SQL_EXEC_SUCCESS}'	=>	"Query execution success",
	'{LANG_EXECUTED_SUCCESSFULLY}'	=>	"executed successfully.",
	'{LANG_SQL_NOCONNECTION}'	=>	"Unable to connect!",
	'{LANG_SQL_DBCONN_TO}'	=>	"Database connection to",
	'{LANG_SQL_DBSELECT_ERROR}'	=>	"Unable to select database!",
	'{LANG_SQL_THEDATABASE}'	=>	"The specified database",
	'{LANG_SQL_COULD_NOT_BE_SELECTED}'	=>	"could not be selected.",
	'{LANG_SQL_COULD_NOT_BE_PROCESSED}'	=>	"could not be processed.",
	'{LANG_SQL_ERROR_MSG_WAS}'	=>	"The mySQL error message was",
	
	/* Loader script */
	'{LANG_LOAD_CORRUPTION}'	=>	"Corruption!",
	'{LANG_LOAD_CORRUPTION_BODY}'	=>	"WhispyForum appears to be installed, however, the configuration file lacks some important variables. It's advised to reinstall the system. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.',
	'{LANG_LOAD_CORRUPTION_ALT}'	=>	"Corrupt configuration",
	'{LANG_LOAD_NOCFG}'	=>	"Configuration file not found!",
	'{LANG_LOAD_NOCFG_BODY}'	=>	"The site's configuration file is missing. It usally means that the engine isn't installed properly. Without configuration, the engine cannot be used, because it can't connect to the database. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.',
	
	/* Template system */
	'{LANG_TEMPLATESYS_TEMP_MISSING}'	=>	"Template missing",
	'{LANG_TEMPLATESYS_TEMP_MISSING_BODY}'	=>	"The specified template file does not exist. This template cannot be displayed.",
	
	/* Login */
	'{LANG_LOGIN}'	=>	"Login",
	'{LANG_LOGIN_ERROR}'	=>	"Login error!",
	'{LANG_LOGIN_SUCCESS}'	=>	"Login success!",
	'{LANG_LOGIN_PLEASEUSEBOX}'	=>	"Please use the login box to login to the site.",
	'{LANG_LOGIN_WRONGPASSWORD}'	=>	"The user you entered does not exist, or you supplied wrong login credientals.",
	'{LANG_LOGIN_CRITICAL_FOR}'	=>	"This variable is critical to properly log you in.",
	'{LANG_LOGIN_DIRECT_OPENING}'	=>	"It seems to be a direct opening of the login page.<br>
When you login, you're automatically redirected through this page by the engine passing vital variables. Those variables cannot be get.",
	'{LANG_LOGIN_SUCCESSFUL}'	=>	"You've successfully logged in.",
	'{LANG_LOGIN_DISABLED}'	=>	"There were errors logging you out. You stay logged in.",
	'{LANG_LOGIN_DISABLED_STACKED_LOGINS}'	=>	"The previous login attempt was rejected due to<br>
session stacking. Login is disabled.",
	'{LANG_LOGIN_CANTHERE}'	=>	"You can login there.",
	'{LANG_MULTIPLE_LOGINS}'	=>	"Multiple logins",
	'{LANG_MULTIPLE_LOGINS_NOSUPPORT}'	=>	"Multiple logins aren't supported",
	'{LANG_MULTIPLE_LOGIN_BODY}'	=>	"Your IP address or session differs from the ones stored in the database.<br>It usally means that you're logged in elsewhere while you want to login here.<br>(It can mean that your account is hijacked as well...)
<br><br>Your session data has been purged form the database. All of your other login isntances will get this error.
<br><br>Please use the login box again to login to your user.",
	
	/* Logout */
	'{LANG_LOGOUT}'	=>	"Logout",
	'{LANG_LOGOUT_ERROR}'	=>	"Logout error!",
	'{LANG_LOGOUT_SUCCESS}'	=>	"Logout success!",
	'{LANG_LOGOUT_PLEASEUSEBOX}'	=>	"Please use the logout box on the main page.",
	'{LANG_LOGOUT_SUCCESS_BODY}'	=>	"You've successfully logged out. Your session has been deleted.",
	'{LANG_LOGOUT_ERROR_BODY}'	=>	"There were errors logging you out. You stay logged in.",
	'{LANG_LOGOUT_DIRECT_OPENING}'	=>	"It seems to be a direct opening of the logout page.<br>
When you logout, you're automatically redirected through this page by the engine passing vital variables. Those variables cannot be get.",
	
	/* Registration */
	'{LANG_REG}'	=>	"Registration",
	'{LANG_REGISTER}'	=>	"Register",
	'{LANG_REGISTER_TOOLTIP}'	=>	"Register new user",
	'{LANG_REG_PASS_MATCH_ERROR}'	=>	"The two passwords you entered do not match. The two passwords must be identical.",
	'{LANG_REG_VAR_NULL_CANNOT_CONTINUE}'	=>	"The registration cannot continue until every required variable is entered.",
	'{LANG_REG_SQL_ERROR}'	=>	"There were errors executing the SQL query to register you. Your new user isn't registered.",
	'{LANG_REG_SUCCESS_TITLE}'	=>	"Register successful!",
	'{LANG_REG_SUCCESS_BODY}'	=>	"You've been registered successfully.",
	'{LANG_REG_ENDED}'	=>	"The registration ended!",
	'{LANG_REG_DATA_INFO}'	=>	"To register a new user, you must enter it's login credientals into the boxes below. Further user information (like sign, profile box, avatar) could be set in the Control Panel after login.",
	'{LANG_REG_DATA_FORM_HEADER}'	=>	"New user's login information",
	
	/* User levels */
	'{LANG_INSUFFICIENT_RIGHTS}'	=>	"Insufficient rights",
	'{LANG_PERMISSIONS_ERROR}'	=>	"User permissions error",
	'{LANG_REQUIRED_ADMIN}'	=>	"This page requires you to have Administrator or higher rights.",
	'{LANG_NO_GUESTS}'	=>	"This page is unaviable for guests",
	'{LANG_REQUIRES_LOGGEDIN}'	=>	"This page requires you to log in to view it's contents.<br><br>Please use the login box to log in to the site. After that, you can view this page.",
	'{LANG_NO_LOGGEDINS}'	=>	"This page is unaviable for registered users",
	'{LANG_REQUIRES_GUEST}'	=>	"This page requires you to be a guest to view it's contents.<br><br>Please use the control box to log out from the site. After that, you can view this page.",
	'{LANG_ADMINISTRATOR}'	=>	"Administrator",
	
	/* Control panel */
	'{LANG_CP_LOWERCASE}'	=>	"control panel",
	'{LANG_CP_UPPERCASE}'	=>	"Control panel",
	'{LANG_UCP_TOOLTIP}'	=>	"User control panel",
	'{LANG_UCP_USER_CUSTOMIZATION}'	=>	"User customization",
	
	/* Avatars */
	'{LANG_USR_YOUR_AVATAR}'	=>	"Your avatar",
	'{LANG_UPLOAD_AVATAR}'	=>	"Upload avatar",
	'{LANG_AVATAR_UPLOAD}'	=>	"Avatar upload",
	'{LANG_AVATAR_YOUR_CURRENT}'	=>	"Your current avatar:",
	'{LANG_AVATAR_UPLOAD_1}'	=>	"To upload a new avatar, browse it and click Upload.",
	'{LANG_AVATAR_UPLOAD_2}'	=>	"Please be sure that your avatar is a",
	'{LANG_AVATAR_UPLOAD_3}'	=>	"Also, your avatar should be greater or equal than",
	'{LANG_AVATAR_UPLOAD_4}'	=>	"(bigger images will be automatically resized).",
	'{LANG_AVATAR_UPLOAD_5}'	=>	"Maximum file size:",
	'{LANG_AVATAR_REDIRECT}'	=>	"You're being redirected to the avatar uploading page...",
	'{LANG_AVATAR_WRONG_FORMAT}'	=>	"The file you wanted to upload is in a wrong format",
	'{LANG_AVATAR_ALLOWED_FORMATS}'	=>	"Allowed formats are:",
	'{LANG_AVATAR_GENERAL_ERROR}'	=>	"There was error uploading your avatar. Usally, this error pops when the upload folder (<tt>upload/usr_avatar</tt>) cannot be written (so the file could not be saved).",
	'{LANG_AVATAR_REFRESH_1}'	=>	"Your avatar has been refreshed successfully.<br>Your new avatar:",
	'{LANG_AVATAR_REFRESH_2}'	=>	"The old avatar's file has been permanently deleted.",
	'{LANG_AVATAR_TOOBIG_1}'	=>	"The file you uploaded is too big! Maximum filesize:",
	'{LANG_AVATAR_TOOBIG_2}'	=>	"MB, but you wanted to upload a big,",
	'{LANG_AVATAR_TOOBIG_3}'	=>	"sized file.",
	
	/* Administrator control panel */
	'{LANG_ACP_TOOLTIP}'	=>	"Administrator control panel",
	
	/* Menu managing */
	'{LANG_MENUS}'	=>	"Menus",
	'{LANG_MENUS_TOOLTIP}'	=>	"Edit menus and menu items",
	'{LANG_MENUS_TITLE}'	=>	"Title",
	'{LANG_MENUS_ALIGN_POSITION}'	=>	"Align position",
	'{LANG_MENUS_SIDE}'	=>	"Side",
	'{LANG_MENUS_MENUBAR}'	=>	"menubar",
	'{LANG_MENUS_NUMOFITEMS}'	=>	"Number of items",
	'{LANG_MENUS_LABEL}'	=>	"Label",
	'{LANG_MENUS_URL}'	=>	"URL",
	'{LANG_MENUS_MENU}'	=>	"Menu",
	'{LANG_MENUS_LIST_ITEMS}'	=>	"List items",
	'{LANG_MENUS_LINK_TYPE}'	=>	"Link type",
	'{LANG_MENUS_INTERNAL}'	=>	"Internal",
	'{LANG_MENUS_EXTERNAL}'	=>	"External",
	'{LANG_MENUS_BACKTOLIST}'	=>	"<< Back to menu list",
	'{LANG_MENUS_REDIRECT_MENULIST}'	=>	"You're being redirected to the menu list.",
	'{LANG_MENUS_MENU_CREATE_HEADER}'	=>	"Create new menu",
	'{LANG_MENUS_MENU_CREATE_BUTTON}'	=>	"Create new menu",
	'{LANG_MENUS_MENU_CREATE_SQL_ERROR}'	=>	"There were errors executing the SQL query to create the new menu. No new menu was created.",
	'{LANG_MENUS_MENU_CREATE_VAR_ERROR}'	=>	"The menu creation cannot be done until every required variable is entered.",
	'{LANG_MENUS_MENU_CREATE_SUCCESS_HEAD}'	=>	"Created the menu",
	'{LANG_MENUS_MENU_CREATE_SUCCESS_1}'	=>	"Creation of menu",
	'{LANG_MENUS_MENU_DELETE_CONFIRM_HEAD}'	=>	"Please confirm deletion of menu",
	'{LANG_MENUS_MENU_DELETE_CONFIRM_1}'	=>	"Deleting this menu will also permanently destroy every items linked to this menu!",
	'{LANG_MENUS_MENU_DELETE_CONFIRM_BUTTON}'	=>	"Delete this menu!",
	'{LANG_MENUS_MENU_DELETE_REJECT_BUTTON}'	=>	"No, I've changed my mind!",
	'{LANG_MENUS_MENU_DELETE_ERROR}'	=>	"The menu could not be deleted",
	'{LANG_MENUS_MENU_DELETE_SUCESS}'	=>	"Menu deleted",
	'{LANG_MENUS_MENU_DELETE_1}'	=>	"The menu was deleted successfully.",
	'{LANG_MENUS_MENU_DELETE_ITEMSDELETED}'	=>	"Menu items deleted",
	'{LANG_MENUS_MENU_DELETE_ITEMSDELETED_BODY}'	=>	"The menu's items were deleted successfully.",
	'{LANG_MENUS_MENU_DELETE_ITEMSNOTDELETED}'	=>	"The menu items could not be deleted",
	'{LANG_MENUS_MENU_DELETE_ITEMSNOTDELETED_BODY}'	=>	"The menu was deleted, but the items failed to do so. This isn't really an issue, becuase when cleanupping, these orphan entries will be cleaned up.",
	'{LANG_MENUS_MENU_MODIFY_BUTTON}'	=>	"Edit menu",
	'{LANG_MENUS_MENU_MODIFY_SQL_ERROR}'	=>	"There were errors executing the SQL query to edit the menu. The menu wasn't modified.",
	'{LANG_MENUS_MENU_MODIFY_VAR_ERROR}'	=>	"The menu edition cannot be done until every required variable is entered.",
	'{LANG_MENUS_MENU_MODIFY_SUCCESS_HEAD}'	=>	"Edited the menu",
	'{LANG_MENUS_MENU_MODIFY_SUCCESS_1}'	=>	"Edition of menu",
	'{LANG_MENUS_ENTRY_CREATE_SUCCESS_1}' =>	"Creation of entry",
	'{LANG_MENUS_ENTRY_CREATE_BUTTON}'	=>	"Add new entry",
	'{LANG_MENUS_ENTRY_CREATE_SQL_ERROR}'	=>	"There were errors executing the SQL query to create the new entry. No new entry was created.",
	'{LANG_MENUS_ENTRY_CREATE_VAR_ERROR}'	=>	"The entry creation cannot be done until every required variable is entered.",
	'{LANG_MENUS_ENTRY_MODIFY_SUCCESS_HEAD}'	=>	"Edited the entry",
	'{LANG_MENUS_ENTRY_MODIFY_SUCCESS_1}'	=>	"Edition of entry",
	'{LANG_MENUS_ENTRY_MODIFY_BUTTON}'	=>	"Edit entry",
	'{LANG_MENUS_ENTRY_MODIFY_SQL_ERROR}'	=>	"There were errors executing the SQL query to edit the entry. The entry wasn't modified.",
	'{LANG_MENUS_ENTRY_MODIFY_VAR_ERROR}'	=>	"The entry edition cannot be done until every required variable is entered.",
	'{LANG_MENUS_ENTRY_DELETE_ERROR}'	=>	"The entry could not be deleted",
	'{LANG_MENUS_ENTRY_DELETE_SUCESS}'	=>	"Entry deleted",
	'{LANG_MENUS_ENTRY_DELETE_1}'	=>	"The entry was deleted successfully.",
	'{LANG_MENUS_BACKTO_ENTRY_LIST}'	=>	"<< Return to entry list",
	
	/* Variable error boxes */
	'{LANG_VAR_REQ_NOT_ENTERED}'	=>	"You didn't entered one of the required variables,",
	'{LANG_VAR_REQ_CANT_BE_EMPTY}'	=>	"cannot be empty."
);
?>