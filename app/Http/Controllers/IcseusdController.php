<?php

namespace Totocsa\Icseusda\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class IcseusdController extends Controller
{
    const array DEFAULT_FIELD = [
        'label' => null,
        'value' => '',
        'filterable' => true,
        'filter' => [
            'value' => '',
            'operator' => '=',
            'boolean' => 'and',
        ],
        'sortable' => true,
    ];

    protected $redirectTo = false;

    public string $currentRouteName;
    public string $title = '';
    public string $header = '';

    public array $defaults = [
        'page' => 1,
        'perPage' => 10,
        'sort' => '',
        'sortDir' => 'asc',
    ];

    public string $modelClass;
    public string $viewIndex = 'icseusda::icseusd.index';
    public array $fields = [];
    public array $perPages = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $this->currentRouteName = Route::currentRouteName();

        $settings = $this->normalize($request);
        if ($this->redirectTo !== false) {
            return redirect($this->redirectTo);
        }

        // ---- query építés ----
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->modelClass::query();

        foreach ($settings['f'] as $field => $v) {
            if ($v['filterable'] && !in_array($v['value'], [null, ''])) {
                $value = strtr($v['filter']['value'], ['{{value}}' => $v['value']]);
                $query->where($field, $v['filter']['operator'], $value, $v['filter']['boolean']);
            }
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $data */
        $data = $query
            ->orderBy($settings['sort'], $settings['sortDir'])
            ->paginate($settings['perPage'])
            ->withQueryString();

        /* Ha az aktuális lap az utolsó lap utánra mutat,
         akkor az aktuális oldal az utolsó lap lesz és redirect.*/
        $currentPage = $data->currentPage();
        $lastPage = $data->lastPage();
        if ($lastPage < $currentPage) {
            $params = $request->query();
            $params['page'] = $lastPage;

            return redirect()->route($this->currentRouteName, $params);
        } else {
            return view($this->viewIndex, array_merge(compact(
                'data',
                'settings',
            ), [
                'currentRouteName' => $this->currentRouteName,
                'title' => $this->title,
                'header' => $this->header,
                'perPages' => $this->perPages,
            ]));
        }
    }

    private function normalize(Request $request): array
    {
        $query = $request->query();
        $settings = array_replace_recursive($this->defaults, $query);

        foreach ($this->fields as $field => $v) {
            $settings['f'][$field] = array_replace_recursive(self::DEFAULT_FIELD, $v);

            if (empty($settings['f'][$field]['label'])) {
                $settings['f'][$field]['label'] = mb_ucfirst($field);
            }

            $value = isset($query['f']) && isset($query['f'][$field]) ? $query['f'][$field] : self::DEFAULT_FIELD['value'];
            if ($value > '') {
                $settings['f'][$field]['value'] = $value;
            }
        }

        // Normalizálás
        if (!($settings['sort'] > '' && isset($settings['f'][$settings['sort']]) && $settings['f'][$settings['sort']]['sortable'])) {
            if ($this->defaults['sort'] > '') {
                $sort = $this->defaults['sort'];
            } else {
                $sort = '';
                $i = 0;
                reset($settings['f']);
                while ($sort == '' && $i < count($settings['f'])) {
                    $field = key($settings['f']);
                    if ($settings['f'][$field]['sortable']) {
                        $sort = $field;
                    }

                    next($settings['f']);
                    $i++;
                }
            }

            $settings['sortDir'] = $this->defaults['sortDir'];

            $params = $request->query();
            $params['sort'] = $sort;

            // A sort és a sortDir ellenőrzése. Ha valamelyik nem jó, akkor Exception lesz.
            $this->modelClass::query()->orderBy($sort, $settings['sortDir'])->first();

            $this->redirectTo = route($this->currentRouteName, $params);
        }

        $settings['sortDir'] = $settings['sortDir'] === 'desc'
            ? 'desc'
            : $this->defaults['sortDir'];

        $settings['page'] = max(1, (int) $settings['page']);

        if (!in_array($settings['perPage'], $this->perPages)) {
            $settings['perPage'] = $this->defaults['perPage'];
        }

        return $settings;
    }
}
