<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use modules\company\application\assembler\CompanyDtoAssembler;
use modules\company\application\dto\CompanyDto;
use modules\company\application\query\ListCompaniesQuery;
use modules\company\domain\repository\ICompanyRepository;
use RuntimeException;

class ListCompaniesHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private CompanyDtoAssembler $companyDtoAssembler,
    ) {}

    /**
     * @return CompanyDto[]
     */
    public function handle(ListCompaniesQuery $query): array
    {
        // Глобальные админы видят все компании, обычные пользователи — только свои
        // Пока для MVP возвращаем по фильтрам без привязки к пользователю
        // TODO: добавить фильтрацию по членству для не-админов

        $offset = ($query->page - 1) * $query->limit;

        $companies = $this->companyRepository->findAll(
            $query->search,
            $query->status,
            $query->limit,
            $offset,
        );

        return $this->companyDtoAssembler->toDtoList($companies);
    }

    public function count(ListCompaniesQuery $query): int
    {
        return $this->companyRepository->countAll(
            $query->search,
            $query->status,
        );
    }
}