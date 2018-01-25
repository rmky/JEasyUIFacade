<?php
use Facebook\WebDriver\WebDriverKeys;
use Codeception\Example;

/**
 * Voraussetzungen:
 * test_usr_german
 * test_usr_english
 * Cache muss fuer Testseiten deaktiviert sein
 */
class euiInputDateCest
{

    public function _before(AcceptanceTester $I)
    {}

    public function _after(AcceptanceTester $I)
    {}

    // *1. Testdialog öffnen.*
    public function loadTestPageGerman(AcceptanceTester $I)
    {
        $I->login('test_usr_german', '12345678');
        
        $I->amOnPage('exface.jeasyuitemplate.euiinputdatetest.html');
        $I->waitForElement('#complaint_date', 30);
        $I->seeInField('input.textbox-text', (new DateTime())->sub(new DateInterval('P1D'))->format('d.m.Y'));
    }

    // *2. Eingabe der nachfolgenden Daten mit der Tastatur, jeweils bestätigen mit Enter.*
    /**
     * @depends loadTestPageGerman
     * @dataprovider dateInputProviderGerman
     */
    public function formatDateGerman(AcceptanceTester $I, Example $example)
    {
        $I->fillField('input.textbox-text', $example['input']);
        $I->waitForElementVisible('div.datebox-calendar-inner', 2);
        $I->pressKey('input.textbox-text', WebDriverKeys::ENTER);
        $I->seeInField('input.textbox-text', $example['output']);
    }

    /**
     * @return array
     */
    protected function dateInputProviderGerman()
    {
        $heute = (new DateTime())->format('d.m.Y');
        $gestern = (new DateTime())->sub(new DateInterval('P1D'))->format('d.m.Y');
        $morgen = (new DateTime())->add(new DateInterval('P1D'))->format('d.m.Y');
        $jahr = (new DateTime())->format('Y');
        
        return [
            ['input' => '30.09.2015', 'output' => '30.09.2015'],
            ['input' => '30-09-2015', 'output' => '30.09.2015'],
            ['input' => '30/09/2015', 'output' => '30.09.2015'],
            ['input' => '05.11.2015', 'output' => '05.11.2015'],
            ['input' => '05-11-2015', 'output' => '05.11.2015'],
            ['input' => '05/11/2015', 'output' => '05.11.2015'],
            ['input' => '5.11.15', 'output' => '05.11.2015'],
            ['input' => '5-11-15', 'output' => '05.11.2015'],
            ['input' => '5/11/15', 'output' => '05.11.2015'],
            ['input' => '2032.09.30', 'output' => '30.09.2032'],
            ['input' => '2032-09-30', 'output' => '30.09.2032'],
            ['input' => '2032/09/30', 'output' => '30.09.2032'],
            ['input' => '32.11.5', 'output' => '05.11.2032'],
            ['input' => '32-11-5', 'output' => '05.11.2032'],
            ['input' => '32/11/5', 'output' => '05.11.2032'],
            ['input' => '30.09', 'output' => '30.09.' . $jahr],
            ['input' => '30-09', 'output' => '30.09.' . $jahr],
            ['input' => '30/09', 'output' => '30.09.' . $jahr],
            ['input' => '5.11', 'output' => '05.11.' . $jahr],
            ['input' => '5-11', 'output' => '05.11.' . $jahr],
            ['input' => '5/11', 'output' => '05.11.' . $jahr],
            ['input' => '3009', 'output' => '30.09.' . $jahr],
            ['input' => '0511', 'output' => '05.11.' . $jahr],
            ['input' => '300915', 'output' => '30.09.2015'],
            ['input' => '051115', 'output' => '05.11.2015'],
            ['input' => '30092015', 'output' => '30.09.2015'],
            ['input' => '05112015', 'output' => '05.11.2015'],
            ['input' => 'Today', 'output' => $heute],
            ['input' => 'Heute', 'output' => $heute],
            ['input' => 'Now', 'output' => $heute],
            ['input' => 'Jetzt', 'output' => $heute],
            ['input' => 'Yesterday', 'output' => $gestern],
            ['input' => 'Gestern', 'output' => $gestern],
            ['input' => 'Tomorrow', 'output' => $morgen],
            ['input' => 'Morgen', 'output' => $morgen],
            ['input' => '0', 'output' => $heute],
            ['input' => '-1', 'output' => $gestern],
            ['input' => '+2d', 'output' => (new DateTime())->add(new DateInterval('P2D'))->format('d.m.Y')],
            ['input' => '-5t', 'output' => (new DateTime())->sub(new DateInterval('P5D'))->format('d.m.Y')],
            ['input' => '1w', 'output' => (new DateTime())->add(new DateInterval('P1W'))->format('d.m.Y')],
            ['input' => '-3w', 'output' => (new DateTime())->sub(new DateInterval('P3W'))->format('d.m.Y')],
            ['input' => '5m', 'output' => (new DateTime())->add(new DateInterval('P5M'))->format('d.m.Y')],
            ['input' => '-1m', 'output' => (new DateTime())->sub(new DateInterval('P1M'))->format('d.m.Y')],
            ['input' => '+1j', 'output' => (new DateTime())->add(new DateInterval('P1Y'))->format('d.m.Y')],
            ['input' => '-10y', 'output' => (new DateTime())->sub(new DateInterval('P10Y'))->format('d.m.Y')]
        ];
    }

    // *3. Auswahl von Daten aus dem Kalender, einmal wenn das Eingabefeld leer ist und einmal wenn im Eingabefeld bereits ein Datum steht.*
    // nicht umsetzbar

    // *4. Eingabe eines relativen Datums z.B. "-1d" und verlassen des Eingabefeldes durch Drücken von Tab.*
    /**
     * @depends formatDateGerman
     */
    public function formatDateGermanOnTab(AcceptanceTester $I)
    {
        $I->fillField('input.textbox-text', '-1d');
        $I->waitForElementVisible('div.datebox-calendar-inner', 2);
        $I->pressKey('input.textbox-text', WebDriverKeys::TAB);
        $I->seeInField('input.textbox-text', (new DateTime())->sub(new DateInterval('P1D'))->format('d.m.Y'));
    }

    // *5. Englische Sprache.*
    public function loadTestPageEnglish(AcceptanceTester $I)
    {
        $I->login('test_usr_english', '12345678');
        
        $I->amOnPage('exface.jeasyuitemplate.euiinputdatetest.html');
        $I->waitForElement('#complaint_date', 30);
        $I->seeInField('input.textbox-text', (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d'));
    }

    /**
     * @depends loadTestPageEnglish
     * @dataprovider dateInputProviderEnglish
     */
    public function formatDateEnglish(AcceptanceTester $I, Example $example)
    {
        $I->fillField('input.textbox-text', $example['input']);
        $I->waitForElementVisible('div.datebox-calendar-inner', 2);
        $I->pressKey('input.textbox-text', WebDriverKeys::ENTER);
        $I->seeInField('input.textbox-text', $example['output']);
    }

    /**
     * @return array
     */
    protected function dateInputProviderEnglish()
    {
        return [
            ['input' => '30.09.2015', 'output' => '2015-09-30'],
            ['input' => '05.11.2015', 'output' => '2015-11-05']
        ];
    }

    // *6. Zeitzone mit negativem Offset.*
    // nicht umsetzbar
}
