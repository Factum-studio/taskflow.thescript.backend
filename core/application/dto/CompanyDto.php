<?php
namespace core\application\dto;

use core\domain\entity\Company;

class CompanyDto implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(Company $company): self
    {
        return new self(
            $company->getId()->value(),
            $company->getName()->value(),
            $company->getDescription()?->value(),
            $company->getCreatedAt()->format('Y-m-d H:i:s'),
            $company->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}