<?php

namespace App\Controller;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GridFunciones;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/export",  name="product_export")
     */
    public function downloadExcel()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Data de Productos");
        $sheet->getCell('A1')->setValue('Code');
        $sheet->getCell('B1')->setValue('Name');
        $sheet->getCell('C1')->setValue('Description');
        $sheet->getCell('D1')->setValue('brand');
        $sheet->getCell('E1')->setValue('price');
        $sheet->fromArray($this->getData(),'', 'A2', true);

        $writer = new Xlsx($spreadsheet);
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename="productsdata.xls"');
        header("Cache-Control: max-age=0");
        try {
            $writer->save('php://output');
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/", name="product_list", methods={"GET"})
     */
    public function list(Request $request, GridFunciones $gridFunciones)
    {
        $em = $this->getDoctrine()->getManager();
        $gridFunciones->init(Product::class, 5, 'code.asc');

        return $this->render('product/list.html.twig',[
            'products'   => $gridFunciones->getRecords(),
            'pager'      => $gridFunciones->getDisplayParameters(),
            'categories' => $em->getRepository(Category::class)->findAllOrder()
        ]);
    }

    /*
     * Obtener data para el reporte
     */
    private function getData()
    {
        $em = $this->getDoctrine()->getManager();
        $list = [];
        $products = $em->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $list[] = [
                $product->getCode(),
                $product->getName(),
                $product->getDescription(),
                $product->getBrand(),
                $product->getPrice()
            ];
        }
        return $list;
    }

}
