<?php
namespace Exceedone\Exment\Console;

trait InstallUpdateTrait
{
    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Create routes file.
     *
     * @return void
     */
    protected function createBootstrapFile()
    {
        $this->directory = config('exment.directory');

        $this->makeDir();

        $file = path_join($this->directory, 'bootstrap.php');

        if (\File::exists($file)) {
            return;
        }

        $contents = $this->getStub('bootstrap');

        $this->laravel['files']->put($file, $contents);
        $this->line('<info>Bootstrap file was created:</info> '.str_replace(base_path(), '', $file));
    }

    /**
     * Get stub contents.
     *
     * @param $name
     *
     * @return string
     */
    protected function getStub($name)
    {
        return $this->laravel['files']->get(path_join(__DIR__, "stubs", "$name.stub"));
    }

    /**
     * Make new directory.
     *
     * @param string $path
     */
    protected function makeDir($path = '')
    {
        $dirpath = $this->directory;

        if (\File::exists($dirpath)) {
            return;
        }

        $this->laravel['files']->makeDirectory($dirpath, 0755, true, true);
    }
}