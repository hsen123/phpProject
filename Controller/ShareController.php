<?php

namespace App\Controller;

use App\Entity\Analysis;
use App\Entity\AnalysisSnapshot;
use App\Entity\Email;
use App\Entity\Result;
use App\Entity\ResultAnalysisSnapshot;
use App\Entity\ResultSnapshot;
use App\Entity\SharedAnalysis;
use App\Entity\SharedResult;
use App\Repository\AnalysisRepository;
use App\Repository\AnalysisSnapshotRepository;
use App\Repository\ResultRepository;
use App\Repository\ResultSnapshotRepository;
use App\Repository\SharedAnalysisRepository;
use App\Service\S3ImageService;
use App\Service\SendShareMailService;
use Aws\S3\S3Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ShareController extends Controller
{
    private static $TYPE_DYNAMIC = 'dynamic';
    private static $TYPE_SNAPSHOT = 'snapshot';

    private static $RESULT = 'result';
    private static $ANALYSIS = 'analysis';

    private $measurementBucketName;
    private $measurementImagePath;
    private $userProfileBucketName;
    private $userProfileImagePath;
    private $s3Client;
    private $imageService;
    private $shareMailService;

    /**
     * @Route(path = "api/create-share/{type}/{entity}")
     * @Method(methods={"POST"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param $type
     * @param $entity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createShareAction(Request $request, EntityManagerInterface $em, $type, $entity)
    {
        $postBody = json_decode($request->getContent());
        $response = new JsonResponse();
        if (!isset($postBody) || !isset($postBody->from) || !isset($postBody->emails)) {
            $response->setContent('Parameters missing')->setStatusCode(400);
            return $response;
        }
        $emails = $postBody->emails;
        $from_id = $postBody->from;
        $shareLink = null;
        switch ($type) {
            case self::$TYPE_DYNAMIC:
                switch ($entity) {
                    case self::$RESULT:
                        /** @var Result $result */
                        $result = $em->getRepository(Result::class)->findOneBy(['id' => $from_id]);
                        if (!$result || $result->getCreatedByUser() !== $this->getUser()) {
                            $response->setContent('Result not found.')->setStatusCode(404);
                            break;
                        }
                        $sharedResult = SharedResult::createSharedResult($result, $emails);
                        $em->persist($sharedResult);
                        $em->flush();
                        $shareLink = $this->generateUrl(
                            'dynamicSharedResult',
                            ['shareId' => $sharedResult->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $this->shareMailService->sendResultDynamicShareMail($shareLink, $emails);
                        break;
                    case self::$ANALYSIS:
                        /** @var Analysis $analysis */
                        $analysis = $em->getRepository(Analysis::class)->findOneBy(['id' => $from_id]);
                        if (!$analysis || $analysis->getUser() !== $this->getUser()) {
                            $response->setContent('Analysis not found.')->setStatusCode(404);
                            break;
                        }
                        $sharedAnalysis = SharedAnalysis::createSharedAnalysis($analysis, $emails);
                        $em->persist($sharedAnalysis);
                        $em->flush();
                        $shareLink = $this->generateUrl(
                            'dynamicSharedAnalysis',
                            ['shareId' => $sharedAnalysis->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $this->shareMailService->sendAnalysisDynamicShareMail($shareLink, $emails);
                        break;
                }
                break;
            case self::$TYPE_SNAPSHOT:
                switch ($entity) {
                    case self::$RESULT:
                        /** @var Result $result */
                        $result = $em->getRepository(Result::class)->findOneBy(['id' => $from_id]);
                        if (!$result || $result->getCreatedByUser() !== $this->getUser()) {
                            $response->setContent('Result not found.')->setStatusCode(404);
                            break;
                        }
                        $snapShot = ResultSnapshot::makeSnapshot($result, $emails);
                        $em->persist($snapShot);
                        $em->flush();
                        $shareLink = $this->generateUrl(
                            'snapshotSharedResult',
                            ['shareId' => $snapShot->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $this->shareMailService->sendResultSnapshotShareMail($shareLink, $emails);
                        break;
                    case self::$ANALYSIS:
                        /** @var Analysis $analysis */
                        $analysis = $em->getRepository(Analysis::class)->findOneBy(['id' => $from_id]);
                        if (!$analysis || $analysis->getUser() !== $this->getUser()) {
                            $response->setContent('Analysis not found.')->setStatusCode(404);
                            break;
                        }
                        $snapShot = AnalysisSnapshot::makeSnapshot($analysis, $emails);
                        $em->persist($snapShot);
                        $em->flush();
                        $shareLink = $this->generateUrl(
                            'snapshotSharedAnalysis',
                            ['shareId' => $snapShot->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $this->shareMailService->sendAnalysisSnapshotShareMail($shareLink, $emails);
                        break;
                }
                break;
        }
        if ($shareLink !== null) {
            $response->setStatusCode(200);
            $response->setContent(json_encode(['shareLink' => $shareLink]));
        } else {
            $response->setStatusCode(404);
            $response->setContent("This action is not available");
        }

        return $response;
    }

    public function __construct(S3ImageService $imageService, SendShareMailService $shareMailService, S3Client $s3Client, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->measurementBucketName = getenv('AWS_S3_MEASUREMENT_IMAGE_BUCKET'); //TODO: Fake s3 needs a space at the end of a bucket name
        $this->measurementImagePath = getenv('AWS_S3_MEASUREMENT_IMAGE_PATH');
        $this->userProfileBucketName = getenv('AWS_S3_USER_PROFILE_IMAGE_BUCKET'); //TODO: Fake s3 needs a space at the end of a bucket name
        $this->userProfileImagePath = getenv('AWS_S3_USER_PROFILE_IMAGE_PATH');
        $this->s3Client = $s3Client;

        //TODO: since fakes3 mistrieats bucket names we have to check if we are in a dev environment and adjust bucket names accordingly
        if ('dev' === getenv('APP_ENV')) {
            $this->measurementBucketName = $this->measurementBucketName . ' ';
            $this->userProfileBucketName = $this->userProfileBucketName . ' ';
        }
        $this->imageService = $imageService;
        $this->shareMailService = $shareMailService;
    }

    /**
     * @Route("/share/dynamic/results/{shareId}", name="dynamicSharedResult")
     * @param $shareId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dynamicSharedResultAction(string $shareId)
    {
        $repo = $this->getDoctrine()->getRepository(SharedResult::class);
        /** @var SharedResult|null $shared */
        $shared = $repo->findOneBy(['id' => $shareId]);
        if (!$shared) {
            throw $this->createNotFoundException();
        }

        return $this->render('share-result/index.html.twig', [
            'result' => $shared->getResult(),
            'title' => $shared->getResult()->getTitle(),
            'shareId' => $shared->getId()
        ]);
    }

    /**
     * @Route("/share/snapshot/results/{shareId}", name="snapshotSharedResult")
     * @param $shareId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function snapshotSharedResultAction(string $shareId)
    {
        if ($this->getUser() === null) {
            throw $this->createAccessDeniedException();
        }
        /** @var ResultSnapshotRepository $repo */
        $repo = $this->getDoctrine()->getRepository(ResultSnapshot::class);
        /** @var ResultSnapshot|null $shared */
        $shared = $repo->findById($shareId);
        if (!$shared) {
            throw $this->createNotFoundException();
        }

        return $this->render('share-result/index.html.twig', [
            'result' => $shared,
            'title' => $shared->getTitle(),
            'shareId' => $shared->getId()
        ]);
    }

    /**
     * @Route("/share/snapshot/analyses/result/{shareId}", name="snapshotSharedAnalysisResult")
     * @param string $shareId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function snapshotSharedAnalysisResultAction(string $shareId)
    {
        if ($this->getUser() === null) {
            throw $this->createAccessDeniedException();
        }
        /** @var ServiceEntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository(ResultAnalysisSnapshot::class);
        /** @var ResultAnalysisSnapshot|null $shared */
        $shared = $repo->findOneBy(['id' => $shareId]);
        if (!$shared) {
            throw $this->createNotFoundException();
        }

        return $this->render('share-result/index.html.twig', [
            'result' => $shared,
            'title' => $shared->getTitle(),
            'shareId' => $shared->getId()
        ]);
    }


    /**
     * @Route("/share/dynamic/analyses/{shareId}/result/{resultId}", name="dynamicSharedAnalysisResult")
     * @param string $shareId
     * @param string $resultId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dynamicSharedAnalysisResultAction(string $shareId, string $resultId)
    {
        /** @var SharedAnalysisRepository $repo */
        $repo = $this->getDoctrine()->getRepository(SharedAnalysis::class);
        /** @var Result $shared */
        $shared = $repo->findOneByShareIdAndResultId($shareId, $resultId);
        if (!$shared) {
            throw $this->createNotFoundException();
        }
        /** @var ResultRepository $repo */
        $resultRepo = $this->getDoctrine()->getRepository(Result::class);
        $result = $resultRepo->findOneBy(['id' => $resultId]);
        return $this->render('share-analyses/dynamicAnalysisResult.html.twig', [
            'result' => $result,
            'title' => $result->getTitle(),
            'analysisShareId' => $shareId
        ]);
    }

    /**
     * @Route("/share/dynamic/analyses/{shareId}", name="dynamicSharedAnalysis")
     * @param $shareId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dynamicSharedAnalysisAction(string $shareId)
    {
        $repo = $this->getDoctrine()->getRepository(SharedAnalysis::class);
        /** @var SharedAnalysis|null $shared */
        $shared = $repo->findOneBy(['id' => $shareId]);
        if (!$shared) {
            throw $this->createNotFoundException();
        }

        return $this->render('share-analyses/index.html.twig', [
            'analysisQueryPath' => $this->generateUrl("api_shared_analyses_analysis_results_get_subresource", ["id" => $shareId]),
            'resultLink' => $this->generateUrl("dynamicSharedAnalysisResult", ["shareId" => $shareId, "resultId" => '__id__']),
            'imageLink' => $this->generateUrl("sharedAnalysisImage", ["shareId" => $shareId, "resultId" => '__id__']),
            'analysis' => $shared->getAnalysis(),
            'title' => $shared->getAnalysis()->getName(),
            'shareId' => $shared->getId()
        ]);
    }

    /**
     * @Route("/share/snapshot/analyses/{shareId}", name="snapshotSharedAnalysis")
     * @param $shareId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function snapshotSharedAnalysisAction(string $shareId)
    {
        if ($this->getUser() === null) {
            throw $this->createAccessDeniedException();
        }
        /** @var AnalysisSnapshotRepository $repo */
        $repo = $this->getDoctrine()->getRepository(AnalysisSnapshot::class);
        /** @var AnalysisSnapshot|null $shared */
        $shared = $repo->findById($shareId);
        if (!$shared) {
            throw $this->createNotFoundException();
        }

        return $this->render('share-analyses/index.html.twig', [
            'analysis' => $shared,
            'analysisQueryPath' => $this->generateUrl("api_analysis_snapshots_result_snapshots_get_subresource", ["id" => $shareId]),
            'resultLink' => $this->generateUrl("snapshotSharedAnalysisResult", ["shareId" => '__id__']),
            'imageLink' => $this->generateUrl("sharedImage", ["shareId" => '__id__']),
            'title' => $shared->getName(),
            'shareId' => $shared->getId()
        ]);
    }

    /**
     * @Route("/share/image/{shareId}", name="sharedImage")
     * @param $shareId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sharedImageAction(string $shareId)
    {
        $dynamicRepo = $this->getDoctrine()->getRepository(SharedResult::class);
        $snapshotRepo = $this->getDoctrine()->getRepository(ResultSnapshot::class);
        /** @var ResultRepository $resultRepository */
        $resultRepository = $this->getDoctrine()->getRepository(Result::class);
        /** @var ServiceEntityRepository $resultAnalysisRepository */
        $resultAnalysisRepository = $this->getDoctrine()->getRepository(ResultAnalysisSnapshot::class);
        /** @var SharedResult|null $shared */
        $shared = $dynamicRepo->findOneBy(['id' => $shareId]);
        $id = null;
        if (!$shared) {
            /** @var ResultSnapshot|null $shared */
            $shared = $snapshotRepo->findOneBy(['id' => $shareId]);
            if (!$shared) {
                /** @var ResultAnalysisSnapshot $shared */
                $shared = $resultAnalysisRepository->findOneBy(['id' => $shareId]);
                $id = $shared->getOriginalResult()->getId();
            } else {
                $id = $shared->getOriginalResult()->getId();
            }
        } else {
            $id = $shared->getResult()->getId();
        }

        return SynchronizationController::sendImage(
            $id,
            $resultRepository,
            $this->imageService,
            $this->s3Client,
            $this->measurementBucketName,
            $this->measurementImagePath
        );
    }

    /**
     * @Route("/share/image/{shareId}/{resultId}", name="sharedAnalysisImage")
     * @param string $shareId
     * @param string $resultId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sharedAnalysisImageAction(string $shareId, string $resultId)
    {
        /** @var SharedAnalysisRepository $dynamicRepo */
        $dynamicRepo = $this->getDoctrine()->getRepository(SharedAnalysis::class);

        /** @var ResultRepository $resultRepository */
        $resultRepository = $this->getDoctrine()->getRepository(Result::class);

        $shared = $dynamicRepo->findOneByShareIdAndResultId($shareId, $resultId);
        if (!$shared) {
            throw $this->createNotFoundException();
        }
        return SynchronizationController::sendImage(
            $resultId,
            $resultRepository,
            $this->imageService,
            $this->s3Client,
            $this->measurementBucketName,
            $this->measurementImagePath
        );
    }

    /**
     * @Route("/share/analysis/links/{id}", name="analysisShareLinks")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getAnalysisShareLinks(Request $request, $id)
    {
        /** @var AnalysisRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Analysis::class);
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        /** @var Analysis $analysis */
        $analysis = $repo->findOneById($id);
        if (!$analysis) {
            throw $this->createNotFoundException();
        }

        if (!$analysis->getUser()->hasRole('ROLE_ADMIN') && $analysis->getUser()->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }

        $shares = [];

        foreach ($analysis->getDynamicShares()->toArray() as $dynamicShare) {
            /** @var SharedAnalysis $dynamicShare */
            $id = $dynamicShare->getId();
            $item = [];
            $item['type'] = 'dynamic';
            $item['id'] = $id;
            $item['link'] = $this->generateUrl(
                'dynamicSharedAnalysis',
                ['shareId' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $item['emails'] = array_map(function (Email $email){
                return $email->getEmailAddress();
            }, $dynamicShare->getEmails()->toArray());
            $shares[] = $item;
        }

        foreach ($analysis->getSnapshotShares()->toArray() as $snapshotShare) {
            /** @var AnalysisSnapshot $snapshotShare */
            $id = $snapshotShare->getId();
            $item = [];
            $item['type'] = 'snapshot';
            $item['id'] = $id;
            $item['validUntil'] = $snapshotShare->getValidUntil();
            $item['emails'] = array_map(function (Email $email){
                return $email->getEmailAddress();
            }, $snapshotShare->getEmails()->toArray());

            $item['link'] = $this->generateUrl(
                'snapshotSharedAnalysis',
                ['shareId' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $shares[] = $item;
        }

        $dynamicItems = $analysis->getDynamicShares()->count();
        $snapshotItems = $analysis->getSnapshotShares()->count();
        $totalItems = $dynamicItems + $snapshotItems;

        return new JsonResponse([
            'shares' => $shares,
            'totalItems' => $totalItems,
            'dynamicItems' => $dynamicItems,
            'snapshotItems' => $snapshotItems,
        ]);
    }


    /**
     * @Route("/share/result/links/{id}", name="resultShareLinks")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getResultShareLinks(Request $request, $id)
    {
        /** @var ResultRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Result::class);
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        /** @var Result $result */
        $result = $repo->findOneById($id);
        if (!$result) {
            throw $this->createNotFoundException();
        }

        if (!$result->getCreatedByUser()->hasRole('ROLE_ADMIN') && $result->getCreatedByUser()->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }

        $shares = [];

        foreach ($result->getDynamicShares()->toArray() as $dynamicShare) {
            /** @var SharedResult $dynamicShare */
            $id = $dynamicShare->getId();
            $item = [];
            $item['type'] = 'dynamic';
            $item['id'] = $id;
            $item['link'] = $this->generateUrl('dynamicSharedResult', ['shareId' => $id], UrlGeneratorInterface::ABSOLUTE_URL);
            $item['emails'] = array_map(function (Email $email){
                return $email->getEmailAddress();
            }, $dynamicShare->getEmails()->toArray());

            $shares[] = $item;
        }

        foreach ($result->getSnapshotShares()->toArray() as $snapshotShare) {
            /** @var ResultSnapshot $snapshotShare */
            $id = $snapshotShare->getId();
            $item = [];
            $item['type'] = 'snapshot';
            $item['id'] = $id;
            $item['validUntil'] = $snapshotShare->getValidUntil();
            $item['emails'] = array_map(function (Email $email){
                return $email->getEmailAddress();
            }, $snapshotShare->getEmails()->toArray());

            $item['link'] = $this->generateUrl('snapshotSharedResult', ['shareId' => $id], UrlGeneratorInterface::ABSOLUTE_URL);
            $shares[] = $item;
        }

        $dynamicItems = $result->getDynamicShares()->count();
        $snapshotItems = $result->getSnapshotShares()->count();
        $totalItems = $dynamicItems + $snapshotItems;

        return new JsonResponse([
            'shares' => $shares,
            'totalItems' => $totalItems,
            'dynamicItems' => $dynamicItems,
            'snapshotItems' => $snapshotItems,
        ]);
    }
}
