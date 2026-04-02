<?php
namespace app\commands;

use core\application\port\ICompanyRepository;
use yii\console\Controller;
use yii\console\ExitCode;

class CompanyCleanCommand extends Controller
{
    public function __construct($id, $module, private ICompanyRepository $companyRepository, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actionCleanEmpty(): int
    {
        $count = $this->companyRepository->removeEmptyCompanies();
        $this->stdout("Removed {$count} empty companies.\n");
        return ExitCode::OK;
    }
}