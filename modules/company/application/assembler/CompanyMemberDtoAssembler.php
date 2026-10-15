<?php

declare(strict_types=1);

namespace modules\company\application\assembler;

use modules\company\domain\entity\CompanyMember;
use modules\company\application\dto\CompanyMemberDto;

class CompanyMemberDtoAssembler
{
    public function toDto(CompanyMember $member): CompanyMemberDto
    {
        return new CompanyMemberDto([
            'companyId' => $member->getCompanyId()->value(),
            'userId' => $member->getUserId()->value(),
            'role' => $member->getRole()->value(),
            'invitedBy' => $member->getInvitedBy()?->value(),
            'invitedAt' => $member->getInvitedAt()?->format('Y-m-d H:i:s'),
            'acceptedAt' => $member->getAcceptedAt()?->format('Y-m-d H:i:s'),
            'joinedAt' => $member->getJoinedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param CompanyMember[] $members
     * @return CompanyMemberDto[]
     */
    public function toDtoList(array $members): array
    {
        return array_map([$this, 'toDto'], $members);
    }
}
