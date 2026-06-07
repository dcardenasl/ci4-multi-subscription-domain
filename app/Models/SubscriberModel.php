<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\SubscriberEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class SubscriberModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'subscribers';
    protected $primaryKey = 'id';
    protected $returnType = SubscriberEntity::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['project_id', 'email', 'status', 'confirm_token', 'unsubscribe_token', 'invitation_code', 'confirmed_at', 'unsubscribed_at'];

    /** @var array<int, string> */
    protected array $searchableFields = [];

    /** @var array<int, string> */
    protected array $filterableFields = ['id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'created_at'];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'email' => 'required|string|max_length[255]',
        'status' => 'required|string|max_length[255]',
        'confirm_token' => 'permit_empty|string|max_length[255]',
        'unsubscribe_token' => 'permit_empty|string|max_length[255]',
        'invitation_code' => 'permit_empty|string|max_length[255]',
        'confirmed_at' => 'permit_empty|valid_date',
        'unsubscribed_at' => 'permit_empty|valid_date',
    ];

    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->select('subscribers.*, projects.name as project_name')
             ->join('projects', 'projects.id = subscribers.project_id', 'left');
        return parent::findAll($limit, $offset);
    }

    public function find($id = null)
    {
        $this->select('subscribers.*, projects.name as project_name')
             ->join('projects', 'projects.id = subscribers.project_id', 'left');
        return parent::find($id);
    }

    public function first()
    {
        $this->select('subscribers.*, projects.name as project_name')
             ->join('projects', 'projects.id = subscribers.project_id', 'left');
        return parent::first();
    }
}
