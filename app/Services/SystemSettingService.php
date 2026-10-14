<?php

namespace App\Services;

use App\Repositories\Interfaces\SystemSettingRepositoryInterface;

/**
 * Service layer for system-level settings.
 */
class SystemSettingService
{
    protected SystemSettingRepositoryInterface $systemSettingRepository;

    public function __construct(SystemSettingRepositoryInterface $systemSettingRepository)
    {
        $this->systemSettingRepository = $systemSettingRepository;
    }

    /**
     * Retrieve the first (and typically only) system settings record.
     *
     * @return \App\Models\SystemSetting|null
     */
    public function getSystemSetting()
    {
        return $this->systemSettingRepository->findById(1);
    }
}
