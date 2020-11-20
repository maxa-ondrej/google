<?php declare(strict_types = 1);

namespace Majksa\Google\Drive\DI;

use Google\Client;
use Nette\DI\CompilerExtension;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class GoogleDriveExtension extends CompilerExtension
{

    /** @var object */
    public $config;

    /**
     * Defining scheme config structure.
     *
     * @return Structure config structure
     */
    public function getConfigSchema(): Structure
    {
        return Expect::structure([
            'name' => Expect::string(),
            'accessType' => Expect::anyOf('online', 'offline')->default('online')->castTo('string'),
            'credentials' => Expect::string(),
            'token' => Expect::string(),
            'prompt' => Expect::string('select_account consent'),
            'scopes' => Expect::arrayOf(Expect::anyOf(
                'drive',
                'drive.appdata',
                'drive.file',
                'drive.metadata',
                'drive.metadata.readonly',
                'drive.photos.readonly',
                'drive.readonly',
                'drive.scripts',
            )->castTo('string')),
        ]);
    }

    /**
     * Loading set configuration.
     * Registers {@link Client} service.
     */
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $scopes = [];
        foreach ($this->config->scopes as $scope) {
            $scopes[] = Client::API_BASE_PATH . '/auth/' . $scope;
        }
        $builder->addDefinition($this->prefix('google'))
            ->setFactory(Client::class, [
                [
                    'application_name' => $this->config->name,
                    'access_type' => $this->config->accessType,
                    'credentials' => $this->config->credentials,
                    'token' => $this->config->token,
                    'prompt' => $this->config->prompt,
                    'scopes' => $scopes,
                ],
            ]);
    }

}
