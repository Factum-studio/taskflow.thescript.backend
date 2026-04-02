<?php
namespace core\application\command;

class UpdateCompanyCommand
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?string $description = null,
        public ?int $updatedBy = null
    ) {}
}