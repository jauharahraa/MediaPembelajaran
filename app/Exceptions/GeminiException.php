<?php

namespace App\Exceptions;

use RuntimeException;

// Pesan exception ini aman ditampilkan kepada siswa (tanpa detail teknis)
class GeminiException extends RuntimeException {}