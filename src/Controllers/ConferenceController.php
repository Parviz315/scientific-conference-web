<?php

declare(strict_types=1);

namespace App\Controllers;

final class ConferenceController extends Controller
{
    private const SECTIONS = [
        [
            'id' => 1,
            'code' => 'CS-AI',
            'title' => 'Искусственный интеллект и анализ данных',
            'chair' => 'д.т.н. Каримов Р. Ш.',
            'room' => 'Ауд. 302',
            'submissions_count' => 14,
        ],
        [
            'id' => 2,
            'code' => 'SE-ARCH',
            'title' => 'Программная инженерия и распределённые системы',
            'chair' => 'к.ф.-м.н. Назарова Д. М.',
            'room' => 'Ауд. 215',
            'submissions_count' => 11,
        ],
        [
            'id' => 3,
            'code' => 'OS-SEC',
            'title' => 'Операционные системы и информационная безопасность',
            'chair' => 'к.т.н. Солиев А. Т.',
            'room' => 'Ауд. 108',
            'submissions_count' => 9,
        ],
    ];

    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'Главная — Организация научной конференции',
            'active' => 'home',
            'conferenceTitle' => 'Республиканская научная конференция молодых исследователей — 2026',
            'conferenceDate' => '2026-12-10',
            'sectionsCount' => count(self::SECTIONS),
            'submissionsCount' => 34,
        ]);
    }

    public function sections(): void
    {
        $this->render('sections/index', [
            'title' => 'Секции конференции',
            'active' => 'sections',
            'sections' => self::SECTIONS,
        ]);
    }
}
