<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Entity\FaqItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Fixtures\Validation\Category;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class FAQController extends Controller
{
    /**
     * @Route("/admin/faqedit", name="faqEdit")
     * @Route("/info/faq", name="infoFaq")
     */
    public function loginAction(Request $request, TranslatorInterface $translator)
    {
        /** @var FaqCategory $allCategories */
        $allCategories = $this->getDoctrine()
            ->getRepository(FaqCategory::class)
            ->findAll();

        $dropDownCategories = [];

        /** @var FaqCategory $category */
        foreach ($allCategories as $category) {
            $dropDownCategories[$category->getName()] = $category->getId();
        }

        $title = $translator->trans('pages.admin.faq_edit.title_normal');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $title = $translator->trans('pages.admin.faq_edit.title_admin');
        }

        return $this->render('faq/index.html.twig', [
            'title' => $title,
            'categories' => $allCategories,
            'dropDownCategories' => $dropDownCategories,
        ]);
    }

    /**
     * @Route("/admin/faq/category", name="postCategory")
     * @Method({"POST"})
     */
    public function postCategory(Request $request, EntityManagerInterface $em)
    {
        $content = json_decode($request->getContent());

        if (false === $this->getUser()->hasRole('ROLE_ADMIN')) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        if (!isset($content->categoryName) && !empty($content->categoryName)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $category = new FaqCategory();
        $category->setVisible(false);
        $category->setName($content->categoryName);
        $em->persist($category);
        $em->flush();

        //Everything went all right
        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/admin/faq/question", name="postQuestion")
     * @Method({"POST"})
     */
    public function postQuestion(Request $request, EntityManagerInterface $em)
    {
        $content = json_decode($request->getContent());

        if (false === $this->getUser()->hasRole('ROLE_ADMIN')) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->isQuestionValid($content)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        /** @var FaqCategory $faqCategory */
        $faqCategory = $this->getDoctrine()
            ->getRepository(FaqCategory::class)
            ->find($content->faqCategory);

        /** @var FaqItem $question */
        $question = new FaqItem();
        $question->setQuestion($content->question);
        $question->setAnswer($content->answer);
        $question->setVisible($content->visible);
        $question->setFaqCategory($faqCategory);
        $em->persist($question);
        $em->flush();

        //Everything went all right
        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/admin/faq/category", name="deleteCategory")
     * @Route("/admin/faq/faqitem", name="deleteFaqItem")
     * @Method({"DELETE"})
     */
    public function deleteFaqItem(Request $request, EntityManagerInterface $em)
    {
        $content = json_decode($request->getContent());

        if (false === $this->getUser()->hasRole('ROLE_ADMIN')) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
        if (!isset($content->id) && !empty($content->id)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $repositoryType = null;

        if ('deleteCategory' === $request->get('_route')) {
            $repositoryType = FaqCategory::class;
        } elseif ('deleteFaqItem' === $request->get('_route')) {
            $repositoryType = FaqItem::class;
        } else {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $itemToDelete = $this->getDoctrine()
            ->getRepository($repositoryType)
            ->find($content->id);

        $em->remove($itemToDelete);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    private function isQuestionValid($questionObj)
    {
        if (!isset($questionObj->question)) {
            return false;
        }
        if (!isset($questionObj->answer)) {
            return false;
        }
        if (!isset($questionObj->visible)) {
            return false;
        }
        if (!isset($questionObj->faqCategory)) {
            return false;
        }

        return true;
    }
}
