<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MakePhpDoc extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:phpdoc {model}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate PHPDoc block for model';

  /**
   * Execute the console command.
   */
  public function handle(): void {
    $model = $this->argument('model');

    // モデル名のバリデーションを追加
    if (!class_exists($model)) {
      $this->error("Model not found: {$model}");
      return;
    }

    // モデル名からファイルパスを生成
    $modelParts = explode('\\', $model);
    $modelName = end($modelParts);
    $modelFilePath = app_path('Models/' . $modelName . '.php');

    // ファイルが存在するかチェック
    if (!file_exists($modelFilePath)) {
      $this->error("Model file not found: {$modelFilePath}");
      return;
    }

    // ファイルの中身を取得
    $fileContent = file_get_contents($modelFilePath);

    $tableName = (new $model)->getTable();
    $columns = Schema::getColumnListing($tableName);

    $phpDoc = "/**\n";
    $phpDoc .= " * Class $model\n";
    $phpDoc .= " *\n";
    foreach ($columns as $column) {
      $phpDoc .= " * @property mixed $column\n";
    }
    $phpDoc .= " *\n";
    $phpDoc .= " * @method static \\$model find(\$id)\n";
    $phpDoc .= " * @method static \\$model create(array \$attributes = [])\n";
    $phpDoc .= " * @method static \\$model updateOrCreate(array \$attributes, array \$values = [])\n";
    $phpDoc .= " * @method static \\$model firstOrCreate(array \$attributes, array \$values = [])\n";
    $phpDoc .= " * @method static \\$model firstOrNew(array \$attributes, array \$values = [])\n";
    $phpDoc .= " * @method static \\$model update(array \$attributes = [], array \$options = [])\n";
    $phpDoc .= " */\n\n";

    // クラス宣言の直前に PHPDoc を挿入する
    $classDeclaration = "class {$modelName}";
    $fileContent = str_replace($classDeclaration, "{$phpDoc}{$classDeclaration}", $fileContent);

    // 上書き保存
    file_put_contents($modelFilePath, $fileContent);

    $this->info('PHPDoc block generated for model: ' . $model);
  }
}
