<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2016, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Controller;

use SP\Auth\Ldap\LdapMsAds;
use SP\Auth\Ldap\LdapStd;
use SP\Core\Exceptions\SPException;
use SP\Http\Request;
use SP\Util\Json;
use SP\Util\Wiki\DokuWikiApi;

/**
 * Class ChecksController
 *
 * @package SP\Controller
 */
class ChecksController implements ItemControllerInterface
{
    use RequestControllerTrait;

    /**
     * ChecksController constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Realizar la acción solicitada en la la petición HTTP
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    public function doAction()
    {
        $type = Request::analyze('type');

        try {
            switch ($type) {
                case 'ldap':
                    $this->checkLdap();
                    break;
                case 'dokuwiki':
                    $this->checkDokuWiki();
                    break;
                default:
                    $this->invalidAction();
            }
        } catch (\Exception $e) {
            $this->jsonResponse->setDescription($e->getMessage());
        }

        Json::returnJson($this->jsonResponse);
    }

    /**
     * Comprobar la conexión a LDAP
     */
    protected function checkLdap()
    {
        $ldapAdsEnabled = Request::analyze('ldap_ads', false, false, true);
        $ldapServer = Request::analyze('ldap_server');
        $ldapBase = Request::analyze('ldap_base');
        $ldapGroup = Request::analyze('ldap_group');
        $ldapBindUser = Request::analyze('ldap_binduser');
        $ldapBindPass = Request::analyzeEncrypted('ldap_bindpass');

        if (!$ldapServer || !$ldapBase || !$ldapBindUser || !$ldapBindPass) {
            $this->jsonResponse->setDescription(_('Los parámetros de LDAP no están configurados'));
            return;
        }

        $Ldap = $ldapAdsEnabled ? $Ldap = new  LdapMsAds() : new  LdapStd();

        $Ldap->setServer($ldapServer);
        $Ldap->setSearchBase($ldapBase);
        $Ldap->setGroup($ldapGroup);
        $Ldap->setBindDn($ldapBindUser);
        $Ldap->setBindPass($ldapBindPass);

        $resCheckLdap = $Ldap->checkConnection();

        if ($resCheckLdap === false) {
            $this->jsonResponse->setDescription(_('Error de conexión a LDAP'));
            $this->jsonResponse->addMessage(_('Revise el registro de eventos para más detalles'));
        } else {
            $this->jsonResponse->setDescription(_('Conexión a LDAP correcta'));
            $this->jsonResponse->addMessage(sprintf(_('Objetos encontrados: %d'), $resCheckLdap));
            $this->jsonResponse->setStatus(0);
        }
    }

    /**
     * Comprobar la conexión a DokuWIki
     */
    protected function checkDokuWiki()
    {
        $dokuWikiUrl = Request::analyze('dokuwiki_url');
        $dokuWikiUser = Request::analyze('dokuwiki_user');
        $dokuWikiPass = Request::analyzeEncrypted('dokuwiki_pass');

        if (!$dokuWikiUrl) {
            $this->jsonResponse->setDescription(_('Los parámetros de DokuWiki no están configurados'));
            return;
        }

        try {
            $DokuWikiApi = DokuWikiApi::checkConnection($dokuWikiUrl, $dokuWikiUser, $dokuWikiPass);

            $dokuWikiVersion = $DokuWikiApi->getVersion();
            $version = is_array($dokuWikiVersion) ? $dokuWikiVersion[0] : _('Error');

            $this->jsonResponse->setDescription(_('Conexión correcta'));
            $this->jsonResponse->addMessage(sprintf('%s: %s', _('Versión'), $version));
            $this->jsonResponse->setStatus(0);
        } catch (SPException $e) {
            $this->jsonResponse->setDescription(_('Error de conexión a DokuWiki'));
            $this->jsonResponse->addMessage(_('Revise el registro de eventos para más detalles'));
        }
    }
}