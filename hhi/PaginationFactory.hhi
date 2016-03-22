<?hh

namespace Caridea\Http;

class PaginationFactory
{
    const DESC = "desc";
    const SORT = "sort";
    const ORDER = "order";
    const PAGE = "page";
    const START_PAGE = "startPage";
    const START_INDEX = "startIndex";
    const START = "start";
    const COUNT = "count";
    const MAX = "max";
    const LIMIT = "limit";
    const OFFSET = "offset";
    const RANGE = "Range";
    const REGEX_RANGE = '/^items=(\\d+)-(\\d+)$/';
    const REGEX_DOJO_SORT = '/^sort\\(.*\\)$/';

    public function create(\Psr\Http\Message\ServerRequestInterface $request, string $sortParameter = self::SORT, array<string,bool> $defaultSort = []): Pagination
    {
        return new Pagination(PHP_INT_MAX, 0, []);
    }

    protected function getOrder(\Psr\Http\Message\ServerRequestInterface $request, string $sortParameter, array<string,bool> $default = []): array<string,bool>
    {
        return [];
    }

    protected function parseSort(string $sort, array<string,bool> &$sorts): void
    {
    }

    protected function parse(array<int,string> &$names, array<string,mixed> &$params, int $defaultValue): int
    {
        return 0;
    }
}
