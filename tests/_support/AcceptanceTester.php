<?php


use Facebook\WebDriver\WebDriverKeys;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */
    
    public function login($username, $password)
    {
        $I = $this;
        
        if ($I->loadSessionSnapshot($username)) {
            return;
        }
        
        $I->amOnPage('login.html');
        $I->fillField('#username', $username);
        $I->fillField('#password', $password);
        $I->pressKey('#password', WebDriverKeys::ENTER);
        $I->waitForElement('#contextBar', 30);
        
        $I->saveSessionSnapshot($username);
    }
    
    public function logout()
    {
        $I = $this;
        
        $I->amOnPage('login.html');
        $I->click('Log out to switch to another user');
        $I->waitForElement('#username', 30);
        
    }
}
