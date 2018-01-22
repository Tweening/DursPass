<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2018, Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\Services\UserGroup;


use SP\Core\Exceptions\SPException;
use SP\Core\Traits\InjectableTrait;
use SP\DataModel\ItemSearchData;
use SP\Repositories\UserGroup\UserGroupRepository;
use SP\Services\ServiceItemTrait;

/**
 * Class UserGroupService
 *
 * @package SP\Services\UserGroup
 */
class UserGroupService
{
    use InjectableTrait;
    use ServiceItemTrait;

    /**
     * @var UserGroupRepository
     */
    protected $userGroupRepository;


    /**
     * UserGroup constructor.
     *
     * @throws \SP\Core\Dic\ContainerException
     */
    public function __construct()
    {
        $this->injectDependencies();
    }

    /**
     * @param UserGroupRepository $userGroupRepository
     */
    public function inject(UserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * @param ItemSearchData $itemSearchData
     * @return \SP\DataModel\ClientData[]
     */
    public function search(ItemSearchData $itemSearchData)
    {
        return $this->userGroupRepository->search($itemSearchData);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->userGroupRepository->getById($id);
    }

    /**
     * @param $id
     * @return $this
     * @throws SPException
     */
    public function delete($id)
    {
        if ($this->userGroupRepository->delete($id) === 0) {
            throw new SPException(SPException::SP_INFO, __u('Grupo no encontrado'));
        }

        return $this;
    }

    /**
     * @param $itemData
     * @return mixed
     * @throws SPException
     */
    public function create($itemData)
    {
        return $this->userGroupRepository->create($itemData);
    }

    /**
     * @param $itemData
     * @return mixed
     * @throws SPException
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function update($itemData)
    {
        return $this->userGroupRepository->update($itemData);
    }

    /**
     * Get all items from the service's repository
     *
     * @return array
     */
    public function getAllBasic()
    {
        return $this->userGroupRepository->getAll();
    }
}