<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;

/**
 * Class IndexController
 *
 * @package App\Http\Controllers
 * @author Miyahara Yuuki <59301668+raikiri-jp@users.noreply.github.com>
 */
class IndexController extends Controller {

  /**
   * 画面表示.
   *
   * @return mixed
   */
  public function frontend(HttpRequest $request) {
    return app('files')->get(public_path('index.html'));
  }
}
