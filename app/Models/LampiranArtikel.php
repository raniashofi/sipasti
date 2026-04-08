<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string        $id
 * @property string        $knowledge_base_id
 * @property string        $nama_file
 * @property string        $path_file
 * @property string        $tipe_file
 * @property int           $ukuran_file
 * @property int           $urutan
 * @property KnowledgeBase $knowledgeBase
 */
class LampiranArtikel extends Model
{
    protected $table = 'lampiran_artikel';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id', 'knowledge_base_id', 'nama_file', 'path_file',
        'tipe_file', 'ukuran_file', 'urutan',
    ];

    protected $casts = [
        'ukuran_file' => 'integer',
        'urutan' => 'integer',
    ];

    /**
     * Relationship: Knowledge Base Article
     */
    public function knowledgeBase()
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    /**
     * Get human-readable file size
     */
    public function getUkuranFileFormatted()
    {
        $bytes = $this->ukuran_file;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get icon based on file type
     */
    public function getIconClass()
    {
        $icons = [
            'pdf' => 'text-red-500',
            'doc' => 'text-blue-500',
            'docx' => 'text-blue-500',
            'xls' => 'text-green-500',
            'xlsx' => 'text-green-500',
            'jpg' => 'text-purple-500',
            'jpeg' => 'text-purple-500',
            'png' => 'text-purple-500',
            'txt' => 'text-gray-500',
        ];

        return $icons[$this->tipe_file] ?? 'text-gray-400';
    }
}
