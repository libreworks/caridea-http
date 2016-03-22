<?hh // strict

namespace Caridea\Http;

class Pagination
{
    protected int $max = PHP_INT_MAX;

    protected int $offset = 0;

    protected array<string,bool> $order = [];
    
    public function __construct(int $max, int $offset, array<string,bool> $order = [])
    {
    }
    
    public function getMax(): int
    {
        return 0;
    }

    public function getOffset(): int
    {
        return 0;
    }

    public function getOrder(): array<string,bool>
    {
        return [];
    }
}
