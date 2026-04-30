<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Manual\ManualRootsReader;

it('reads manual roots tsv entries with full columns', function (): void {
    $path = tempManualRootsPath('full');

    file_put_contents($path, implode(PHP_EOL, [
        '# form	root	lemma	pos_cat	pos	language	confidence',
        'مد	مدد	مد	فعل	فعل	فصحى	0.99',
        'أقلام	قلم	قلم	اسم	جمع تكسير	فصحى	0.98',
        '',
    ]));

    $entries = (new ManualRootsReader)->read($path);

    expect($entries)->toHaveCount(2)
        ->and($entries[0]->form)->toBe('مد')
        ->and($entries[0]->root)->toBe('مدد')
        ->and($entries[0]->lemma)->toBe('مد')
        ->and($entries[0]->posCat)->toBe('فعل')
        ->and($entries[0]->pos)->toBe('فعل')
        ->and($entries[0]->language)->toBe('فصحى')
        ->and($entries[0]->confidence)->toBe(0.99)
        ->and($entries[1]->form)->toBe('أقلام')
        ->and($entries[1]->root)->toBe('قلم')
        ->and($entries[1]->lemma)->toBe('قلم')
        ->and($entries[1]->posCat)->toBe('اسم')
        ->and($entries[1]->pos)->toBe('جمع تكسير')
        ->and($entries[1]->language)->toBe('فصحى')
        ->and($entries[1]->confidence)->toBe(0.98);
});

it('reads minimal manual roots rows', function (): void {
    $path = tempManualRootsPath('minimal');

    file_put_contents($path, implode(PHP_EOL, [
        'قال	قول',
        'باع	بيع',
    ]));

    $entries = (new ManualRootsReader)->read($path);

    expect($entries)->toHaveCount(2)
        ->and($entries[0]->form)->toBe('قال')
        ->and($entries[0]->root)->toBe('قول')
        ->and($entries[0]->lemma)->toBe('قال')
        ->and($entries[0]->posCat)->toBeNull()
        ->and($entries[0]->pos)->toBeNull()
        ->and($entries[0]->language)->toBe('فصحى')
        ->and($entries[0]->confidence)->toBe(0.98)
        ->and($entries[1]->form)->toBe('باع')
        ->and($entries[1]->root)->toBe('بيع');
});

it('ignores empty lines and comments', function (): void {
    $path = tempManualRootsPath('comments');

    file_put_contents($path, implode(PHP_EOL, [
        '',
        '# short doubled roots',
        'مد	مدد',
        '',
        '# weak hollow roots',
        'قال	قول',
        '',
    ]));

    $entries = (new ManualRootsReader)->read($path);

    expect($entries)->toHaveCount(2)
        ->and($entries[0]->form)->toBe('مد')
        ->and($entries[1]->form)->toBe('قال');
});

it('rejects rows with fewer than form and root columns', function (): void {
    $path = tempManualRootsPath('invalid-short-row');

    file_put_contents($path, "مد\n");

    expect(fn () => (new ManualRootsReader)->read($path))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects rows with empty form or root', function (string $line): void {
    $path = tempManualRootsPath('invalid-empty-column');

    file_put_contents($path, $line);

    expect(fn () => (new ManualRootsReader)->read($path))
        ->toThrow(InvalidArgumentException::class);
})->with([
    "\tمدد",
    "مد\t",
]);

it('throws when manual roots file is missing', function (): void {
    expect(fn () => (new ManualRootsReader)->read('/missing/manual-roots.tsv'))
        ->toThrow(RuntimeException::class);
});

function tempManualRootsPath(string $suffix): string
{
    return sys_get_temp_dir().'/dujana-manual-roots-'.$suffix.'-'.uniqid().'.tsv';
}
