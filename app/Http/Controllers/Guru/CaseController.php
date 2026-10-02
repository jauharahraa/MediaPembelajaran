<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CaseController extends Controller
{
    private const CASE_FIELDS = ['title', 'narrative', 'supporting_info', 'instructions'];
    private const RUBRIC_FIELDS = [
        'identifikasi_indikator', 'identifikasi_kriteria',
        'analisis_indikator', 'analisis_kriteria',
        'solusi_indikator', 'solusi_kriteria',
    ];

    public function create(Room $room)
    {
        Gate::authorize('manage', $room);

        return view('guru.cases.form', ['room' => $room, 'case' => new CaseStudy]);
    }

    public function store(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);
        $data = $this->validated($request);

        DB::transaction(function () use ($room, $data) {
            $case = $room->cases()->create(Arr::only($data, self::CASE_FIELDS) + [
                'sort_order' => (CaseStudy::where('room_id', $room->id)->max('sort_order') ?? 0) + 1,
            ]);
            $case->rubric()->create(Arr::only($data, self::RUBRIC_FIELDS));
        });

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Kasus dan rubrik berhasil ditambahkan.');
    }

    public function show(Room $room, CaseStudy $case)
    {
        $this->authorizeCase($room, $case);
        $case->load('rubric');

        return view('guru.cases.show', compact('room', 'case'));
    }

    public function edit(Room $room, CaseStudy $case)
    {
        $this->authorizeCase($room, $case);
        $case->load('rubric');

        return view('guru.cases.form', compact('room', 'case'));
    }

    public function update(Request $request, Room $room, CaseStudy $case)
    {
        $this->authorizeCase($room, $case);
        $data = $this->validated($request);

        DB::transaction(function () use ($case, $data) {
            $case->update(Arr::only($data, self::CASE_FIELDS));
            $case->rubric()->updateOrCreate(['case_id' => $case->id], Arr::only($data, self::RUBRIC_FIELDS));
        });

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Kasus dan rubrik berhasil diperbarui.');
    }

    public function destroy(Room $room, CaseStudy $case)
    {
        $this->authorizeCase($room, $case);
        $case->delete(); // jawaban, feedback, dan nilai siswa pada kasus ini ikut terhapus

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Kasus berhasil dihapus.');
    }

    public function reorder(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);

        $ids = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        $owned = CaseStudy::where('room_id', $room->id)->pluck('id')->all();
        abort_unless(count(array_diff($ids, $owned)) === 0, 422, 'Urutan kasus tidak valid.');

        foreach ($ids as $i => $id) {
            CaseStudy::where('id', $id)->where('room_id', $room->id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    private function authorizeCase(Room $room, CaseStudy $case): void
    {
        Gate::authorize('manage', $room);
        abort_unless($case->room_id === $room->id, 404);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'                  => ['required', 'string', 'max:200'],
            'narrative'              => ['required', 'string'],
            'supporting_info'        => ['nullable', 'string'],
            'instructions'           => ['nullable', 'string'],
            'identifikasi_indikator' => ['required', 'string'],
            'identifikasi_kriteria'  => ['required', 'string'],
            'analisis_indikator'     => ['required', 'string'],
            'analisis_kriteria'      => ['required', 'string'],
            'solusi_indikator'       => ['required', 'string'],
            'solusi_kriteria'        => ['required', 'string'],
        ]);
    }
}