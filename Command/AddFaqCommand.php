<?php

namespace App\Command;

use App\Entity\FaqCategory;
use App\Entity\FaqItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddFaqCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:add-faq')

            // the short description shown while running "php bin/console list"
            ->setDescription('Adds the faq texts.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to add the content of the faq section.')
        ;
    }

    /**
     * Command to add the FAQs of Mquant StripScan
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'FAQ Creator',
            '====================',
            'Start adding FAQs...',
            '',
        ]);

        $appCategory = new FaqCategory();
        $appCategory->setName('MQuant® StripScan App ');
        $appCategory->setVisible(true);
        $this->manager->persist($appCategory);

        $stripCategory = new FaqCategory();
        $stripCategory->setName('MQuant® Test Strips');
        $stripCategory->setVisible(true);
        $this->manager->persist($stripCategory);

        $webCategory = new FaqCategory();
        $webCategory->setName('MQuant® StripScan Web');
        $webCategory->setVisible(true);
        $this->manager->persist($webCategory);

        $appQuestion1 = new FaqItem();
        $appQuestion1->setFaqCategory($appCategory);
        $appQuestion1->setQuestion('How do I perform a measurement using the MQuant® StripScan app?');
        $appQuestion1->setAnswer('<p>The <em>How To</em> section in the <em>Help</em> menu of the app provides several resources to support you- for instance short animations or detailed written instructions. Please also check our supporting materials online: <a href="http://www.sigmaaldrich.com/mquant-stripscan" target="_blank">www.sigmaaldrich.com/mquant-stripscan</a></p>');
        $appQuestion1->setVisible(true);
        $this->manager->persist($appQuestion1);

        $appQuestion2 = new FaqItem();
        $appQuestion2->setFaqCategory($appCategory);
        $appQuestion2->setQuestion('The strip readout did not work- what went wrong?');
        $appQuestion2->setAnswer('<p>If there is an issue during the camera acquisition of your test strip, a message at the bottom of the screen will usually explain what to do. For a successful readout, please check the messages and follow its instructions.</p><p>Here are some general tips to obtain robust results:</p><p>The phone camera: Make sure your camera functions properly: Check if the camera lens is dirty, if your camera shows signs of focus issues, that your image section is the correct size to allow alignment of the crop marks, and that your crop marks on the viewfinder of your phone screen are aligned with those on the reference card. </p><p>Reference card: Make sure your reference card is in good condition: It should not be scratched, bent, or stained. Photocopied cards will not work. Also check that you are using the correct Reference Card for the parameter that you want to measure. </p><p>If you find that your card is damaged, you can acquire replacements at <a target="_blank" href="http://www.sigmaaldrich.com/mquant-stripscan" rel="noopener noreferrer">www.sigmaaldrich.com/mquant-stripscan</a>. </p><p>Lighting: Make sure to provide good lighting conditions: Are you measuring in an area with little or very bright light? Are there reflections or shadows on the card? Diffuse lighting and medium brightness are ideal for measurements. </p><p>Strip placement: Make sure you place the strip with the reaction zone facing upwards and aligned with the marks of the Reference Card.</p>');
        $appQuestion2->setVisible(true);
        $this->manager->persist($appQuestion2);

        $appQuestion3 = new FaqItem();
        $appQuestion3->setFaqCategory($appCategory);
        $appQuestion3->setQuestion('I can only choose between two parameters, pH and nitrate. Will there be more?');
        $appQuestion3->setAnswer('<p>We are just getting started with StripScan. You can look forward to receiving more functionality as we continue to expand the app for the MQuant<sup>®</sup> portfolio- We will keep you posted for updates!</p>');
        $appQuestion3->setVisible(true);
        $this->manager->persist($appQuestion3);

        $appQuestion4 = new FaqItem();
        $appQuestion4->setFaqCategory($appCategory);
        $appQuestion4->setQuestion('Where can I get a Reference Card?');
        $appQuestion4->setAnswer('<p>You can buy MQuant<sup>® </sup>StripScan Reference Cards online from late summer 2018 (see e-shop links on <a target="_blank" href="http://www.sigmaaldrich.com/mquant-stripscan" rel="noopener noreferrer">www.sigmaaldrich.com/mquant-stripscan</a>). Please also check your local retailer.</p>');
        $appQuestion4->setVisible(true);
        $this->manager->persist($appQuestion4);

        $appQuestion5 = new FaqItem();
        $appQuestion5->setFaqCategory($appCategory);
        $appQuestion5->setQuestion('I do not have a MQuant® StripScan Reference Card. Can I also measure without it? Can I use a photocopy?');
        $appQuestion5->setAnswer('<p>No. The MQuant<sup>®</sup> StripScan Reference Card ensures reliable and robust results by providing an external color standard- Only this way we can provide the level of reliability and confidence in the results of MQuant<sup>®</sup> StripScan. Please note that due to the sophisticated production process and the brilliance and colorfastness of the colors on the Reference Card, the use of photocopies will not yield correct results.</p>');
        $appQuestion5->setVisible(true);
        $this->manager->persist($appQuestion5);

        $appQuestion6 = new FaqItem();
        $appQuestion6->setFaqCategory($appCategory);
        $appQuestion6->setQuestion('Can I use the MQuant® StripScan system with test strips from other manufacturers?');
        $appQuestion6->setAnswer('<p>No, the MQuant<sup>®</sup> StripScan system was specifically calibrated to work with the color reactions obtained by MQuant<sup>®</sup> test strips (and pH strips formerly marketed as MColorpHast<sup>®</sup>, now MQuant<sup>®</sup>). While the use of other manufacturers’ strips may yield result values, these values will not be correct since they have not been calibrated with the app algorithm.</p>');
        $appQuestion6->setVisible(true);
        $this->manager->persist($appQuestion6);

        $appQuestion7 = new FaqItem();
        $appQuestion7->setFaqCategory($appCategory);
        $appQuestion7->setQuestion('Can I use Reflectoquant® strips with the app?');
        $appQuestion7->setAnswer('<p>No, the MQuant<sup>®</sup> StripScan system was specifically calibrated to work with the color reactions obtained by MQuant<sup>®</sup> test strips. While the use of Reflectoquant<sup>®</sup> test strips may yield result values, however these will not be correct since they have not been calibrated with the app algorithm.</p>');
        $appQuestion7->setVisible(true);
        $this->manager->persist($appQuestion7);

        $appQuestion8 = new FaqItem();
        $appQuestion8->setFaqCategory($appCategory);
        $appQuestion8->setQuestion('Why did I receive a timeout message during image acquisition?');
        $appQuestion8->setAnswer('<p>The color of the test strip may intensify over time, and the readout works in a defined time frame. A progress bar at the bottom of the acquisition screen lets you know how much time is left to perform the readout. It is important to use a fresh strip when repeating the measurement. </p>');
        $appQuestion8->setVisible(true);
        $this->manager->persist($appQuestion8);

        $appQuestion9 = new FaqItem();
        $appQuestion9->setFaqCategory($appCategory);
        $appQuestion9->setQuestion('Why did I receive the error “Wrong Reference Card”?');
        $appQuestion9->setAnswer('<p>Please make sure the Reference Card corresponds with the test parameter you selected in the app. To obtain Reference Cards, please see the e-shop links on <a href="http://www.sigmaaldrich.com/mquant-stripscan" target="_blank" rel="noopener noreferrer">www.sigmaaldrich.com/mquant-stripscan</a>, or check your local retailer.</p>');
        $appQuestion9->setVisible(true);
        $this->manager->persist($appQuestion9);

        $appQuestion10 = new FaqItem();
        $appQuestion10->setFaqCategory($appCategory);
        $appQuestion10->setQuestion('Why did I receive a note that my measured value is out of range?');
        $appQuestion10->setAnswer('<p>MQuant<sup>®</sup> test strips work within a certain concentration range, as stated on the label of the test strips. If the analyte concentration in your sample exceeded this range, you can dilute or concentrate your sample, or choose an alternative MQuant<sup>®</sup> product with suitable range. </p>');
        $appQuestion10->setVisible(true);
        $this->manager->persist($appQuestion10);

        $appQuestion11 = new FaqItem();
        $appQuestion11->setFaqCategory($appCategory);
        $appQuestion11->setQuestion('How do I know if my sample is suitable for analysis with MQuant® StripScan?');
        $appQuestion11->setAnswer('<p>If you successfully used MQuant<sup>®</sup> strips with your sample before, they will most likely work with MQuant<sup>®</sup> StripScan. Please check the section “Applications” in the instructions of your MQuant<sup>®</sup> test strip package, or our application notes on <a href="http://www.sigmaaldrich.com/mquant-stripscan" target="_blank" rel="noopener noreferrer">www.sigmaaldrich.com/mquant-stripscan</a> for more details. </p>');
        $appQuestion11->setVisible(true);
        $this->manager->persist($appQuestion11);

        $appQuestion12 = new FaqItem();
        $appQuestion12->setFaqCategory($appCategory);
        $appQuestion12->setQuestion('Why is my geoposition not shown in the result data of my measurement?');
        $appQuestion12->setAnswer('<p>Please make sure your phone’s GPS is turned on, and that StripScan is allowed access to GPS data.</p>');
        $appQuestion12->setVisible(true);
        $this->manager->persist($appQuestion12);

        $appQuestion13 = new FaqItem();
        $appQuestion13->setFaqCategory($appCategory);
        $appQuestion13->setQuestion('Is the StripScan App also available in languages other than English?');
        $appQuestion13->setAnswer('<p>We are working on it! As of now, StripScan is available in English only.</p>');
        $appQuestion13->setVisible(true);
        $this->manager->persist($appQuestion13);

        $appQuestion14 = new FaqItem();
        $appQuestion14->setFaqCategory($appCategory);
        $appQuestion14->setQuestion('How are the result values displayed in MQuant® StripScan?');
        $appQuestion14->setAnswer('<p>The pH is given in gradations of 0,5 pH units. </p><p> The nitrate values are displayed in the following gradation: </p><p> 0 – 5- 10 – 15 – 20 – 25 – 35 – 50 – 75 – 100 – 250 – 500 – &gt;500 mg/l.</p>');
        $appQuestion14->setVisible(true);
        $this->manager->persist($appQuestion14);

        $appQuestion15 = new FaqItem();
        $appQuestion15->setFaqCategory($appCategory);
        $appQuestion15->setQuestion('Synchronization of my data failed. What went wrong?');
        $appQuestion15->setAnswer('<p>Make sure you have internet connection, and try synchronizing again by pulling down on the result list.</p>');
        $appQuestion15->setVisible(true);
        $this->manager->persist($appQuestion15);

        $appQuestion16 = new FaqItem();
        $appQuestion16->setFaqCategory($appCategory);
        $appQuestion16->setQuestion('Why can I skip the reaction time?');
        $appQuestion16->setAnswer('<p>If you have already finished incubation of your test strip, or for serial measurements, you can override the reaction countdown. If you skip the timer however, make sure to monitor the correct reaction time yourself.</p>');
        $appQuestion16->setVisible(true);
        $this->manager->persist($appQuestion16);

        $appQuestion17 = new FaqItem();
        $appQuestion17->setFaqCategory($appCategory);
        $appQuestion17->setQuestion('On which phones can the app be used?');
        $appQuestion17->setAnswer('<p>MQuant<sup>®</sup> StripScan is currently available for iPhones with iOS 10 and higher.</p>');
        $appQuestion17->setVisible(true);
        $this->manager->persist($appQuestion17);

        $appQuestion18 = new FaqItem();
        $appQuestion18->setFaqCategory($appCategory);
        $appQuestion18->setQuestion('Why can\'t I delete results in the app?');
        $appQuestion18->setAnswer('<p>In the StripScan app, you can discard a result only immediately after acquisition. To manage, share and delete your results, please use StripScan web (registration required).</p>');
        $appQuestion18->setVisible(true);
        $this->manager->persist($appQuestion18);

        $stripQuestion1 = new FaqItem();
        $stripQuestion1->setFaqCategory($stripCategory);
        $stripQuestion1->setQuestion('What strip batches can be used?');
        $stripQuestion1->setAnswer('<p>Strips from production year 2018 and later will work with MQuant<sup>®</sup> StripScan. Older batches may also function with MQuant<sup>®</sup> StripScan; however, please compare the results with those of a current batch if you want to use the app with an older test strip batch.</p>');
        $stripQuestion1->setVisible(true);
        $this->manager->persist($stripQuestion1);

        $webQuestion1 = new FaqItem();
        $webQuestion1->setFaqCategory($webCategory);
        $webQuestion1->setQuestion('I do not see my newest measurement data on MQuant® StripScan Web. What went wrong?');
        $webQuestion1->setAnswer('<p>Make sure your phone is connected to the internet. You can synchronize the data manually by pulling down on the result list in the app.</p>');
        $webQuestion1->setVisible(true);
        $this->manager->persist($webQuestion1);

        $this->manager->flush();

        $output->writeln('Creating FAQs finished!');
    }
}