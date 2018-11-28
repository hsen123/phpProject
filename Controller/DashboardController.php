<?php

namespace App\Controller;

use App\Entity\Result;
use App\Entity\TestStripPackage;
use App\Repository\ResultRepository;
use App\Repository\TestStripPackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DashboardController extends Controller
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * DashboardController constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @param Request $request
     * @param RouterInterface $router
     * @return Response
     */
    public function dashboardAction(Request $request, RouterInterface $router)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var TestStripPackageRepository $repo */
        $repo = $this->getDoctrine()->getRepository(TestStripPackage::class);
        $user = $this->getUser();

        $no3 = $repo->findForUserAndCitation($user, Result::CITATION_NO3);
        $ph = $repo->findForUserAndCitation($user, Result::CITATION_PH);

        $needsFlush = false;
        if (!$no3) {
            $no3 = new TestStripPackage();
            $no3->setUser($user);
            $no3->setCitationForm(Result::CITATION_NO3);
            $em->persist($no3);
            $needsFlush = true;
        }

        if (!$ph) {
            $ph = new TestStripPackage();
            $ph->setUser($user);
            $ph->setCitationForm(Result::CITATION_PH);
            $em->persist($ph);
            $needsFlush = true;
        }

        if ($needsFlush) {
            $em->flush();
        }

        $testStripNo3Results = $this->serializer->serialize($no3, 'jsonld');
        $testStripPhResults = $this->serializer->serialize($ph, 'jsonld');

        return $this->render('dashboard/dashboard.html.twig', [
            'user' => $user,
            'testStripNo3Results' => $testStripNo3Results,
            'testStripPhResults' => $testStripPhResults,
        ]);
    }

    /**
     * @Route("/api/dashboard/activity", name="dashboard_activity")
     * @param Request $request
     * @param RouterInterface $router
     * @return JsonResponse
     */
    public function userActivity(Request $request, RouterInterface $router)
    {

        if (!$request->query->has("in")) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST, "message" => 'missing query parameter "in"']);
        }

        $timeZoneOffset = $this->determineTimezoneOffset($request);
        $timestamp = intval($request->query->get("in"));
        if ($timestamp < 0) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST, "message" => 'parameter "in" must be non negative']);
        }

        $user = $this->getUser();
        /** @var ResultRepository $resultRepo */
        $resultRepo = $this->getDoctrine()->getRepository(Result::class);
        $groupedResultsCount = $resultRepo->getGroupedResultCountForWeek($router, $timestamp, $timeZoneOffset, $user);

        return JsonResponse::create($groupedResultsCount);
    }

    /**
     * @param Request $request
     * @return int|mixed
     */
    private function determineTimezoneOffset(Request $request)
    {
        $timeZone = new \DateTimeZone(getenv("TIME_ZONE"));
        $defaultTimeZoneOffset = $timeZone->getOffset(new \DateTime("now", $timeZone));
        $timeZoneOffset = $request->query->has("tzo") ? intval($request->query->get("tzo")) * 60 : $defaultTimeZoneOffset;
        return $timeZoneOffset;
    }

    /**
     * Adjusts amount of strips in a package, but only one at a time is possible.
     *
     * @Route("/api/dashboard/adjustPackageCount", name="dashboard_adjust_package")
     * @Method(methods={"POST"})
     * @param Request $request
     * @param TestStripPackageRepository $testStripPackageRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function adjustPackageCount(Request $request, TestStripPackageRepository $testStripPackageRepository, EntityManagerInterface $em)
    {
        $body = json_decode($request->getContent());
        $id = $body->id;
        $delta = (int)$body->delta;
        if (!$id) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST]);
        }
        if (!$delta) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST]);
        }
        if ($delta !== 1 && $delta !== -1) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST]);
        }

        /** @var TestStripPackage $package */
        $package = $testStripPackageRepository->findOneBy(["id" => $id]);
        if (!$package) {
            throw $this->createNotFoundException();
        }
        $newCount = $package->getStartAmount() + $delta;
        $amountMeasurements = $package->getResults()->count();
        if ($newCount > 100 || $newCount < $amountMeasurements) {
            return new JsonResponse(["code" => Response::HTTP_BAD_REQUEST]);
        }
        $package->setAmountOfTestStripsLeft($newCount - $amountMeasurements);
        $package->setStartAmount($newCount);
        $em->persist($package);
        $em->flush();
        return JsonResponse::create(["updated" => true]);
    }

}
