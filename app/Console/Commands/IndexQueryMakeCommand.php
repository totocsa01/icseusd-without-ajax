<?php

namespace Totocsa\Icseusda\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// It was created based on make:model.
#[AsCommand(name: 'make:indexquery')]
class IndexQueryMakeCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:indexquery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new index query class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'IndexQuery';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return Command::FAILURE;
        } else {
            $argName = $this->argument('name');

            return Command::SUCCESS;
        }
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'IndexQuery.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/indexquery.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('IndexQueries')) ? $rootNamespace . '\\IndexQueries' : $rootNamespace;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    /*protected function buildClass($name)
    {
        $modelClassShortName = array_last(explode('\\', $name));
        $modelFullName = "App\\Models\\$modelClassShortName";

        $defaults = [];
        $fields = ["            '" . Str::singular(Str::lower($modelClassShortName)) . "id' => [
                'select' => '" . Str::plural(Str::lower($modelClassShortName)) . ".id',
                'alias' => '" . Str::singular(Str::lower($modelClassShortName)) . "id',
                'label' => 'ID',
                'visible' => false,
                'filterable' => false,
                'sortable' => false,
            ],"];
        $rules = [];

        $fillable = new $modelFullName()->getFillable();
        foreach ($fillable as $v) {
            $defaults[] = "                '$v' => '',";
            $fields[] = "            '$v',";
            $rules[] = "            '$v'  => ['required', 'string', 'max:100'],";
        }

        return strtr(parent::buildClass($name), [
            '{{ modelClassName }}' => "App\\Models\\$modelClassShortName",
            '{{ modelClassShortName }}' => $modelClassShortName,
            '{{ indexQueryClassName }}' => "{$modelClassShortName}IndexQuery",
            '{{ defaults }}' => implode("\n", $defaults),
            '{{ fields }}' => implode("\n", $fields),
            '{{ rules }}' => implode("\n", $rules),
        ]);
    }*/

    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model for which the index query is being created.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, ''],
        ];
    }
}
