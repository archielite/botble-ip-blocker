<?php

namespace ArchiElite\IpBlocker\Tables;

use ArchiElite\IpBlocker\Models\History;
use ArchiElite\IpBlocker\Repositories\Interfaces\IpBlockerInterface;
use Botble\Base\Facades\BaseHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Botble\Table\Supports\Builder;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;

class HistoryTable extends TableAbstract
{
    protected $view = 'core/table::simple-table';

    protected $hasCheckbox = false;

    protected $hasOperations = false;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, IpBlockerInterface $ipBlockerRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $ipBlockerRepository;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('updated_at', function (History $item) {
                return BaseHelper::formatDateTime($item->updated_at);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'ip_address',
            'count',
            'updated_at',
        ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'ip_address' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.ip_address'),
                'class' => 'text-start',
            ],
            'count' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.count'),
                'class' => 'text-start',
            ],
            'updated_at' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.last_visited'),
                'width' => 'text-start',
            ],
        ];
    }
}
