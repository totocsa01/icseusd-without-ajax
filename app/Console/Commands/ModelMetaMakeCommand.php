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
#[AsCommand(name: 'make:modelmeta')]
class ModelMetaMakeCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:modelmeta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model meta class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ModelMeta';

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

            $this->info('In the model:');
            $this->line("    public function meta(): {$argName}Meta");
            $this->line("    {");
            $this->line("        return new {$argName}Meta(\$this);");
            $this->line("    }");

            return Command::SUCCESS;
        }
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'Meta.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/modelmeta.stub');
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
        return is_dir(app_path('ModelMetas')) ? $rootNamespace . '\\ModelMetas' : $rootNamespace;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $modelClassShortName = array_last(explode('\\', $name));
        $modelFullName = "App\\Models\\$modelClassShortName";

        $labels = [];
        $rules = [];
        $fillable = new $modelFullName()->getFillable();
        foreach ($fillable as $v) {
            $labels[] = "            '$v' => '" . Str::ucfirst($v) . "',";
            $rules[] = "            '$v'  => ['required', 'string', 'max:100'],";
        }

        return strtr(parent::buildClass($name), [
            '{{ modelClassName }}' => "App\\Models\\$modelClassShortName",
            '{{ modelClassShortName }}' => $modelClassShortName,
            '{{ metaClassName }}' => "{$modelClassShortName}Meta",
            '{{ labels }}' => implode("\n", $labels),
            '{{ rules }}' => implode("\n", $rules),
        ]);
    }

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
            ['name', InputArgument::REQUIRED, 'The name of the model for which the model meta is being created.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, ''],
        ];
    }
}
