<?hh

namespace Caridea\Http;

class QueryParams
{
    public static function get(array<string,mixed> $server): array<string,mixed>
    {
        return [];
    }
    
    protected static function normalize(array<int,string> $pair): array<int,string>
    {
        return [];
    }
    
    public static function getFromServer(): array<string,mixed>
    {
        return [];
    }
}
