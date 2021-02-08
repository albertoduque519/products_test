<?php


namespace App\Service;


use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class GridFunciones
{
    private $sortField;
    private $sortDirection;
    private $sortReverse;
    private $pageSize;
    private $currentPage;
    private $totalPages;
    private $totalRecords;
    private $offset;
    private $filter;
    private $em;
    private $request;

    protected $requestStack;

    public function __construct(RequestStack $requestStack, Container $container)
    {
        $this->requestStack = $requestStack;
        $this->em = $container->get('doctrine')->getManager();
    }
    /*
     * Metodo para inicializar
     */
    public function init($entity_name, $page_size = 20, $default_sort_field = null) {
        $this->request =  $this->requestStack->getCurrentRequest();
        $this->entityName = $entity_name;
        $this->pageSize = $page_size;
        $this->defaultSortField = $default_sort_field;
        $this->setFilter();
        $this->totalRecords = $this->getTotal();
        $this->setCurrentPage();
        $this->setSorting();
    }

    /*
     * Metodo que se encarga de devolver las variables para la paginacion
     * filtro y orden
     * return @retunr [] Variales utitlizadas
     */
    public function getDisplayParameters()
    {
        $return = array(
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'sort_field' => $this->sortField,
            'sort_order' => $this->sortDirection,
            'sort_reverse' => $this->sortReverse,
        );
        if(empty($this->sortField)) {
            $return['sort'] = '';
        } else {
            $return['sort'] = $this->sortField . '.' . strtolower($this->sortDirection);
        }
        $return['filter'] = $this->filter;

        return $return;
    }

    /*
     * Metodo que se encarga de obtener todos los registros
     * aplicando los filtros de busqueda y ordenacion
     * return @records
     */
    public function getRecords()
    {

        $records = $this->em->getRepository(Product::class)
            ->createQueryBuilder('t');
        if(!empty($this->sortField)) {
            $records = $records->orderBy('t.' . $this->sortField, $this->sortDirection);
        }

        if(!empty($this->filter)) {
            foreach($this->filter as $key => $value) {
                if( $this->em->getClassMetadata(Product::class)->hasField($key) ) {
                    $records = $records->andWhere("t.{$key} LIKE :{$key}");
                    if( $this->em->getClassMetadata(Product::class)->getTypeOfField($key) === 'string' ||
                        $this->em->getClassMetadata(Product::class)->getTypeOfField($key) === 'text' ) {
                        $records = $records->setParameter($key, '%' . $value . '%');
                    } else {
                        $records = $records->setParameter($key, $value);
                    }
                }
                if( $this->em->getClassMetadata(Product::class)->hasAssociation($key) ) {
                    $records = $records->andWhere("t.{$key} = :{$key}");
                    $records = $records->setParameter($key, $value);
                }
            }
        }
        $records = $records->setFirstResult($this->offset)
            ->setMaxResults($this->pageSize)
            ->getQuery()
            ->getResult();

        return $records;
    }

    /*
     * Metodo que se encarga de realizar la ordenacion
     */
    public function setSorting()
    {
        $sort = $this->request->get('sort');
        if(empty($sort) && empty($this->defaultSortField)) {
            $this->sortField = '';
            $this->sortDirection = '';
        } else {
            if(empty($sort)) {
                $arr = explode('.', $this->defaultSortField);
            } else {
                $arr = explode('.', $sort);
            }
            if(empty($arr[0])) {
                $this->sortField = '';
                $this->sortDirection = '';
            } elseif(count($arr) == 1 || empty($arr[1])) {
                $this->sortField = $arr[0];
                $this->sortDirection = 'ASC';
                $this->sortReverse = $this->sortField . '.desc';
            } else {
                $this->sortField = $arr[0];
                if(strtolower($arr[1]) == 'desc') {
                    $this->sortDirection = 'DESC';
                    $this->sortReverse = $this->sortField . '.asc';
                } else {
                    $this->sortDirection = 'ASC';
                    $this->sortReverse = $this->sortField . '.desc';
                }
            }

            if( !$this->em->getClassMetadata(Product::class)->hasField($this->sortField) ) {
                $this->sortField = '';
                $this->sortDirection = '';
            }
        }
    }

    /*
     * Metodo que se encargar de realizar la paginacion
     */
    public function setCurrentPage()
    {
        $this->pageSize = 5;
        $this->currentPage = $this->request->get('page');

        if(empty($this->currentPage)) {
            $this->currentPage = 1;
        }
        $this->totalPages = ceil($this->totalRecords/$this->pageSize);
        if(($this->currentPage * $this->pageSize) > $this->totalRecords) {
            $this->currentPage = $this->totalPages;
        }

        if($this->currentPage > 1) {
            $this->offset = ($this->currentPage - 1) * $this->pageSize;
        } else {
            $this->offset = 0;
        }
    }

    /*
     * Metodo que se encarga de obtener el total de registros a paginar
     */
    public function getTotal()
    {
        $total = $this->em->getRepository(Product::class)
            ->createQueryBuilder('t')
            ->select('count(t.id)');
        if(!empty($this->filter)) {
            foreach($this->filter as $key => $value) {
                if( $this->em->getClassMetadata(Product::class)->hasField($key) ) {
                    $total = $total->andWhere("t.{$key} LIKE :{$key}");
                    if( $this->em->getClassMetadata(Product::class)->getTypeOfField($key) === 'string' ||
                        $this->em->getClassMetadata(Product::class)->getTypeOfField($key) === 'text' ) {
                        $total = $total->setParameter($key, '%' . $value . '%');
                    } else {
                        $total = $total->setParameter($key, $value);
                    }
                }
            }
        }
        $total = $total->getQuery()->getSingleScalarResult();

        return $total;
    }

    /*
     * Metodo que se encarga de cargar todas las variables del filtro
     */
    public function setFilter()
    {
        $this->filter = array();
        $filters = $this->request->get('filter');
        if(is_array($filters)) {
            foreach($filters as $key => $value) {
                if(!empty($value) || $value == '0') {
                    $this->filter[$key] = $value;
                }
            }
        }
    }
}