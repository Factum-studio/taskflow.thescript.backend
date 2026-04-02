<?php
namespace core\application\query;

class GetCompanyUsersQuery
{
    public function __construct(public int $companyId) {}
}