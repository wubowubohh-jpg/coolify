<?php

namespace App\Livewire\Server\New;

use App\Enums\ProxyTypes;
use App\Models\PrivateKey;
use App\Models\Server;
use App\Models\Team;
use App\Rules\ValidServerIp;
use App\Support\ValidationPatterns;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ByIp extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public $private_keys;

    #[Locked]
    public $limit_reached;

    public ?int $private_key_id = null;

    public $new_private_key_name;

    public $new_private_key_description;

    public $new_private_key_value;

    public string $name;

    public ?string $description = null;

    public string $ip;

    public string $user = 'root';

    public int $port = 22;

    public bool $is_build_server = false;

    public function mount()
    {
        $this->name = generate_random_name();
        $this->private_key_id = $this->private_keys->first()?->id;
    }

    protected function rules(): array
    {
        return [
            'private_key_id' => 'nullable|integer',
            'new_private_key_name' => 'nullable|string',
            'new_private_key_description' => 'nullable|string',
            'new_private_key_value' => 'nullable|string',
            'name' => ValidationPatterns::nameRules(),
            'description' => ValidationPatterns::descriptionRules(),
            'ip' => ['required', 'string', new ValidServerIp],
            'user' => ValidationPatterns::serverUsernameRules(),
            'port' => 'required|integer|between:1,65535',
            'is_build_server' => 'required|boolean',
        ];
    }

    protected function messages(): array
    {
        return array_merge(ValidationPatterns::combinedMessages(), [
            'private_key_id.integer' => __('validation.custom.private_key_id.integer'),
            'private_key_id.nullable' => __('validation.custom.private_key_id.nullable'),
            'new_private_key_name.string' => __('validation.custom.new_private_key_name.string'),
            'new_private_key_description.string' => __('validation.custom.new_private_key_description.string'),
            'new_private_key_value.string' => __('validation.custom.new_private_key_value.string'),
            'ip.required' => __('validation.custom.ip.required'),
            'ip.string' => __('validation.custom.ip.string'),
            'user.required' => __('validation.custom.user.required'),
            'user.string' => __('validation.custom.user.string'),
            ...ValidationPatterns::serverUsernameMessages(),
            'port.required' => __('validation.custom.port.required'),
            'port.integer' => __('validation.custom.port.integer'),
            'port.between' => __('validation.custom.port.between'),
            'is_build_server.required' => __('validation.custom.is_build_server.required'),
            'is_build_server.boolean' => __('validation.custom.is_build_server.boolean'),
        ]);
    }

    public function getListeners(): array
    {
        return [
            'privateKeyCreated' => 'handlePrivateKeyCreated',
        ];
    }

    public function setPrivateKey(string $private_key_id)
    {
        $this->private_key_id = $private_key_id;
    }

    public function generatePrivateKey(string $type): void
    {
        try {
            $this->authorize('create', PrivateKey::class);

            if (! in_array($type, ['ed25519', 'rsa'], true)) {
                $this->dispatch('error', __('common.invalid_private_key_type'));

                return;
            }

            $keyData = PrivateKey::generateNewKeyPair($type);
            $privateKey = PrivateKey::createAndStore([
                'name' => $keyData['name'],
                'description' => $keyData['description'],
                'private_key' => $keyData['private_key'],
                'team_id' => currentTeam()->id,
            ]);

            $this->handlePrivateKeyCreated($privateKey->id);
            $this->dispatch('success', __('common.private_key_created_successfully'));
        } catch (\Throwable $e) {
            handleError($e, $this);
        }
    }

    public function handlePrivateKeyCreated($keyId): void
    {
        $this->private_keys = PrivateKey::ownedAndOnlySShKeys()->where('id', '!=', 0)->get();
        $this->private_key_id = $keyId;
        $this->resetErrorBag('private_key_id');
    }

    public function instantSave()
    {
        // $this->dispatch('success', 'Application settings updated!');
    }

    public function submit()
    {
        $this->validate();
        try {
            $this->authorize('create', Server::class);
            $foundServer = Server::whereIp($this->ip)->first();
            if ($foundServer) {
                if ($foundServer->team_id === currentTeam()->id) {
                    return $this->dispatch('error', __('common.server_exists_team'));
                }

                return $this->dispatch('error', __('common.server_exists_other_team'));
            }

            if (is_null($this->private_key_id)) {
                return $this->dispatch('error', __('common.select_private_key_required'));
            }
            if (Team::serverLimitReached()) {
                return $this->dispatch('error', __('common.server_limit_reached'));
            }
            $payload = [
                'name' => $this->name,
                'description' => $this->description,
                'ip' => $this->ip,
                'user' => $this->user,
                'port' => $this->port,
                'team_id' => currentTeam()->id,
                'private_key_id' => $this->private_key_id,
            ];
            if ($this->is_build_server) {
                data_forget($payload, 'proxy');
            }
            $server = Server::create($payload);
            $server->proxy->set('status', 'exited');
            $server->proxy->set('type', ProxyTypes::TRAEFIK->value);
            $server->save();
            $server->settings->is_build_server = $this->is_build_server;
            $server->settings->save();

            return redirectRoute($this, 'server.show', [$server->uuid]);
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }
}
