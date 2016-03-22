<?hh // strict

namespace Caridea\Http;

class AcceptTypes
{
    public function __construct<T>(array<string,T> $server)
    {
    }

    public function preferred(array<string> $types) : ?string
    {
        return null;
    }
}
