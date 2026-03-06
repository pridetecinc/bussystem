<?php

namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Models\masters\PdfTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfTemplateController extends Controller
{
    /**
     * 显示模板列表
     */
    public function index(Request $request)
    {
        $query = PdfTemplate::query();

        // 搜索功能：支持模板名称、语言代码、文件路径
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('template_name', 'like', "%{$search}%");
            });
        }

        // 排序：sort 字段数字越大越靠前 (DESC)，如果 sort 相同则按 ID 倒序
        $templates = $query->orderBy('sort', 'desc')->orderBy('id', 'desc')->paginate(20);

        // 保持搜索参数在分页链接中
        if ($request->has('search')) {
            $templates->appends(['search' => $request->search]);
        }

        return view('masters.pdf_templates.index', compact('templates'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        return view('masters.pdf_templates.create');
    }

    /**
     * 保存新模板
     */
    public function store(Request $request)
    {
        // 验证规则
        $rules = [
            'template_name' => 'required|string|max:100',
            'language_code' => 'required|string|max:10',
            'template_file' => 'required|file|mimes:doc,docx|max:10240', // 最大 10MB
            'sort'          => 'nullable|integer|min:1|max:9999',
        ];

        // 日文错误消息
        $messages = [
            'template_name.required' => 'テンプレート名は必須です。',
            'template_name.max'      => 'テンプレート名は100文字以内で入力してください。',
            'language_code.required' => '対応言語は必須です。',
            'language_code.max'      => '対応言語は10文字以内で入力してください。',
            'template_file.required' => 'テンプレートファイル (Word) は必須です。',
            'template_file.file'     => '有効なファイルをアップロードしてください。',
            'template_file.mimes'    => 'Word ファイル (.doc, .docx) のみをアップロードできます。',
            'template_file.max'      => 'ファイルサイズは 10MB 以下にしてください。',
            'sort.integer'           => 'ソート順は整数で入力してください。',
            'sort.min'               => 'ソート順は1以上で入力してください。',
            'sort.max'               => 'ソート順は9999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        $validated['sort'] = (int)$validated['sort'];

        try {
            //处理文件上传
            if ($request->hasFile('template_file')) {
                $file = $request->file('template_file');
                
                // 1. 获取扩展名
                $extension = $file->getClientOriginalExtension();
                
                // 2. 生成唯一文件名：时间戳_随机字符串.扩展名
                $filename = time() . bin2hex(random_bytes(2)) . '.' . $extension;
                
                // 3. 构建动态目录路径：files/templates/年/月日
                // date('Y') -> 2026, date('md') -> 0306
                $directory = '/storage/files/templates/' . date('Y') . '/' . date('md');
                
                // 4. 确保目录存在 (storage/app/public/... 下)
                // Storage::disk('public') 对应 storage/app/public 目录
                Storage::disk('public')->makeDirectory($directory);
                
                // 5. 保存文件到指定目录
                // 最终物理路径：storage/app/public/files/templates/2026/0306/filename.docx
                $path = $file->storeAs($directory, $filename, 'public');
                
                // 6. 将相对路径存入 validated 数组
                $validated['template_file'] = $path;
            }

            PdfTemplate::create($validated);
            return redirect()->route('masters.pdf_templates.index')
                ->with([
                    'success' => 'PDFテンプレートを登録しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            // 这里可以记录日志 Log::error($e);
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 显示模板详情
     */
    public function show(PdfTemplate $pdfTemplate)
    {
        return view('masters.pdf_templates.show', compact('pdfTemplate'));
    }

    /**
     * 显示编辑表单
     */
    public function edit(PdfTemplate $pdfTemplate)
    {
        return view('masters.pdf_templates.edit', compact('pdfTemplate'));
    }

    /**
     * 更新模板
     */
    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        // 验证规则 (更新时不需要 unique 检查，除非你有 template_code 且需要唯一)
        $rules = [
            'template_name' => 'required|string|max:100',
            'language_code' => 'required|string|max:10',
            'template_file' => 'file|mimes:doc,docx|max:10240', // 最大 10MB
            'sort'          => 'nullable|integer|min:0|max:9999',
        ];

        $messages = [
            'template_name.required' => 'テンプレート名は必須です。',
            'template_name.max'      => 'テンプレート名は100文字以内で入力してください。',
            'language_code.required' => '対応言語は必須です。',
            'language_code.max'      => '対応言語は10文字以内で入力してください。',
            'template_file.required' => 'テンプレートファイル (Word) は必須です。',
            'template_file.file'     => '有効なファイルをアップロードしてください。',
            'template_file.mimes'    => 'Word ファイル (.doc, .docx) のみをアップロードできます。',
            'template_file.max'      => 'ファイルサイズは 10MB 以下にしてください。',
            'sort.integer'           => 'ソート順は整数で入力してください。',
            'sort.min'               => 'ソート順は0以上で入力してください。',
            'sort.max'               => 'ソート順は9999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        // 处理 sort 字段
        if (!isset($validated['sort']) || $validated['sort'] === '' || $validated['sort'] === null) {
            $validated['sort'] = 0;
        } else {
            $validated['sort'] = (int)$validated['sort'];
        }

        try {
            if ($request->hasFile('template_file')) {
                $file = $request->file('template_file');
                
                // 1. 获取扩展名
                $extension = $file->getClientOriginalExtension();
                
                // 2. 生成唯一文件名：时间戳_随机字符串.扩展名
                $filename = time() . bin2hex(random_bytes(2)) . '.' . $extension;
                
                // 3. 构建动态目录路径：files/templates/年/月日
                // date('Y') -> 2026, date('md') -> 0306
                $directory = '/storage/files/templates/' . date('Y') . '/' . date('md');
                
                // 4. 确保目录存在 (storage/app/public/... 下)
                // Storage::disk('public') 对应 storage/app/public 目录
                Storage::disk('public')->makeDirectory($directory);
                
                // 5. 保存文件到指定目录
                // 最终物理路径：storage/app/public/files/templates/2026/0306/filename.docx
                $path = $file->storeAs($directory, $filename, 'public');
                
                // 6. 将相对路径存入 validated 数组
                $validated['template_file'] = $path;
            }
            $pdfTemplate->update($validated);

            return redirect()->route('masters.pdf_templates.index')
                ->with([
                    'success' => 'PDFテンプレートを更新しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /**
     * 删除模板
     */
    public function destroy(PdfTemplate $pdfTemplate)
    {
        try {
            $pdfTemplate->delete();

            return redirect()->route('masters.pdf_templates.index')
                ->with([
                    'success' => 'PDFテンプレートを削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            return redirect()->route('masters.pdf_templates.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}