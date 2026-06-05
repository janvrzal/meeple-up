<?php

class HomeController extends Controller
{
    public function index(): void
    {
        if (!Auth::check()) {
            $this->render('home');           // guest landing
            return;
        }

        $data = (new DashboardService())->forUser(Auth::id());
        $data['user'] = Auth::user();
        $this->render('dashboard', $data);
    }
}