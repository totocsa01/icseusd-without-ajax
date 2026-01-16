<?php

namespace Totocsa\Icseusda\app\Console\Commands;

use DirectoryIterator;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

// It was created based on make:model.
#[AsCommand(name: 'make:icseusdaviews')]
class IcseusdaViewsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:icseusdaviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a icseusda controller views';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'IcseusdaViews';

    protected array $allStubs = [];
    protected array $currentStubs = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $handle = true;
        $this->setStubs();

        $modelName = $this->getNameInput();
        $replaces = [
            '{{ modelName }}' => Str::ucfirst($modelName),
            '{{ lowerModelName }}' => Str::lower($modelName),
            '{{ pluralModelName }}' => Str::plural(Str::ucfirst($modelName)),
            '{{ pluralLowerModelName }}' => Str::plural(Str::lower($modelName)),
        ];

        $viewsPath = resource_path('views') . DIRECTORY_SEPARATOR . Str::plural(Str::lower($modelName));
        if (!is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
            $this->components->info("The $viewsPath directory is created.");
        } else {
            $this->components->info("The $viewsPath directory already exists.");
        }

        foreach ($this->currentStubs as $viewName => $path) {
            $content = file_get_contents($path);

            if ($viewName === 'form') {
                $formItems = $this->getFormItems("App\\Models\\$modelName");
                $content = strtr($content, ['{{ formItems }}' => $formItems]);
            } else if ($viewName === 'show') {
                $showItems = $this->getShowItems("App\\Models\\$modelName");
                $content = strtr($content, ['{{ showItems }}' => $showItems]);
            }


            $content = strtr($content, $replaces);
            $filePath = $viewsPath . DIRECTORY_SEPARATOR . "$viewName.blade.php";
            $isFile = is_file($filePath);
            $writable = !$isFile || $this->option('force');

            if ($writable) {
                $this->files->put($filePath, $content);
                $this->components->info("The $filePath file is " . ($isFile ? 'overwritten' : 'created') . ".");
            } else {
                $this->components->error("$filePath was not written.");
                $handle = false;
            }
        }

        if ($handle) {
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }
    }

    protected function setStubs(): void
    {
        $resourcesArray = array_values(
            array_filter(
                array_map('trim', explode(',', $this->input->getOption('resources')))
            )
        );

        $iterator = new DirectoryIterator(__DIR__ . '/stubs/views');

        $this->allStubs = [];
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $this->allStubs[$fileinfo->getBasename('.stub')] = $fileinfo->getRealPath();
            }
        }

        if (count($resourcesArray) === 1 && $resourcesArray[0] === 'all') {
            $this->currentStubs = $this->allStubs;
        } else {
            $this->currentStubs = [];
            foreach ($resourcesArray as $v) {
                $this->currentStubs[$v] = $this->allStubs[$v];
            }
        }
    }

    protected function getFormItems($modelFullName): string
    {
        $fillable = new $modelFullName()->getFillable();

        $contentArray = [];
        foreach ($fillable as $v) {
            $contentArray[] = '    <div class="form-item">' . PHP_EOL
                . "        <label for=\"$v\" class=\"label\">{{ \${{ lowerModelName }}->meta()->label('$v') }}</label>" . PHP_EOL
                . "        <input id=\"$v\" name=\"$v\" type=\"text\" value=\"{{ old('$v', \${{ lowerModelName }}->$v) }}\"'" . PHP_EOL
                . '            class="input">' . PHP_EOL . PHP_EOL
                . "        @error('$v')" . PHP_EOL
                . '            <div class="error">' . PHP_EOL
                . '                {{ $message }}' . PHP_EOL
                . '            </div>' . PHP_EOL
                . '        @enderror' . PHP_EOL
                . '    </div>';
        }

        return implode(PHP_EOL . PHP_EOL, $contentArray);
    }

    protected function getShowItems($modelFullName): string
    {
        $fillable = new $modelFullName()->getFillable();

        foreach (['created_at', 'updated_at'] as $v) {
            if (array_search($v, $fillable) === false) {
                $fillable[] = $v;
            }
        }

        $contentArray = [];
        foreach ($fillable as $v) {
            $contentArray[] = '        <div class="table-row odd:bg-blue-200 even:bg-blue-100">' . PHP_EOL
                . "            <div class=\"table-cell font-bold pb-0.5 pl-1 pr-1 pt-0.5\">{{ \${{ lowerModelName }}->meta()->label('$v') }}</div>" . PHP_EOL
                . "            <div class=\"table-cell pb-0.5 pl-1 pr-1 pt-0.5\">{{ \${{ lowerModelName }}->$v }}</div>" . PHP_EOL
                . '        </div>';
        }

        return implode(PHP_EOL . PHP_EOL, $contentArray);
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/Http/Views/' . str_replace('\\', '/', $name) . 'View.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/views/.stub');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model for which the icseusda views is being created.'],
        ];
    }

    protected function getOptions()
    {
        $viewsArray = [];
        $iterator = new DirectoryIterator(__DIR__ . '/stubs/views');

        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $viewsArray[] = $fileinfo->getBasename('.stub');
            }
        }

        sort($viewsArray, SORT_STRING);
        $views = implode(', ', $viewsArray);

        return [
            ['resources', 'r', InputOption::VALUE_OPTIONAL, "Views that will be created."
                . " Its value is either a comma-separated list of views"
                . " or 'all' and all views will be created.\n"
                . "Available views: $views\n", 'all'],
            ['force', 'f', InputOption::VALUE_NONE, ''],
        ];
    }
}
