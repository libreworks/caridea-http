<?hh

namespace Caridea\Http;

use Psr\Http\Message\UriInterface;

class ProblemDetails
{
    const MIME_TYPE_JSON = 'application/problem+json';
    const REGEX_NAMES = '/^[a-z][a-z0-9_]{2,}$/i';

    protected ?UriInterface $type;
    protected ?string $title;
    protected int $status = 0;
    protected ?string $detail;
    protected ?UriInterface $instance;
    protected array<string,mixed> $extensions = [];
    protected array<string,mixed> $output = [];

    public function __construct(?UriInterface $type = null, ?string $title = null, int $status = 0, ?string $detail = null, ?UriInterface $instance = null, array<string,mixed> $extensions = [])
    {
    }
    
    public function getType(): ?UriInterface
    {
        return null;
    }

    public function getTitle(): ?string
    {
        return "";
    }

    public function getStatus(): int
    {
        return 0;
    }

    public function getDetail(): ?string
    {
        return "";
    }

    public function getInstance(): ?UriInterface
    {
        return null;
    }

    public function getExtensions(): array<string,mixed>
    {
        return [];
    }

    public function __toString(): string
    {
        return "";
    }

    public function toJson(): string
    {
        return "";
    }

    public function toArray(): array<string,mixed>
    {
        return [];
    }
}
