<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentAspect;
use App\Models\Facility;
use App\Models\Feature;
use App\Models\GalleryCategory;
use App\Models\MissionPoint;
use App\Models\Photo;
use App\Models\StaffMember;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'features' => Feature::published()->ordered()->get(),
            'latestPhotos' => Photo::published()->ordered()->whereNotNull('image_path')->take(4)->get(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'missionPoints' => MissionPoint::published()->ordered()->get(),
            'developmentAspects' => DevelopmentAspect::published()->ordered()->get(),
            'facilities' => Facility::published()->ordered()->get(),
        ]);
    }

    public function strukturOrganisasi(): View
    {
        return view('pages.struktur-organisasi');
    }

    public function sdm(): View
    {
        return view('pages.sdm', [
            'staff' => StaffMember::published()->ordered()->get(),
        ]);
    }

    public function sdmShow(StaffMember $staff): View
    {
        abort_unless($staff->status === 'published', 404);

        return view('pages.sdm-show', [
            'member' => $staff->load('educationHistories'),
        ]);
    }

    public function gallery(): View
    {
        return view('pages.gallery', [
            'categories' => GalleryCategory::query()->ordered()->get(),
            'photos' => Photo::published()->ordered()->whereNotNull('image_path')->get(),
        ]);
    }
}
