<?php

namespace App\Filament\Pos\Pages;

use Filament\Pages\Page;

class PosTerminalPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected string $view = 'filament.pos.pages.pos-terminal-page';
    
    protected static ?string $title = 'Terminal de Caja';
    
    protected static ?string $slug = ''; // Esto hace que sea la ruta raíz del panel /pos
}
