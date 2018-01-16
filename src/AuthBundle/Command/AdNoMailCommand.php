<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdNoMailCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * @var ActiveDirectoryNotification
     */
    private $activeDirectoryNotification;

    /**
     * AdFixNameCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory Active directory Service
     *
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     */
    public function __construct(ActiveDirectory $activeDirectory, ActiveDirectoryNotification $activeDirectoryNotification)
    {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:no:mail')
            ->setDescription('Synchronise the AD with GO4HR exception data');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void null or 0 if everything went fine, or an error code
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var ActiveDirectoryResponse[] $logs
         */
        foreach ($this->getUsers() as $email) {
            $logs[] = $this->activeDirectory->noMail($email);
        }

        $table = new Table($output);
        $table->setHeaders([
            'message',
            'status',
            'type',
            'data',
        ]);

        $rows = [];

        foreach ($logs as $log) {
            $rows[] = [
                'message' => $log->getMessage(),
                'status' => $log->getStatus(),
                'type' => $log->getType(),
                'data' => json_encode($log->getData()),
            ];
        }
        $table->setRows($rows);
        $table->render();

    }

    private function getUsers()
    {
        return [
            'evariste.nikiema@btcctb.org',
            'abdoul.kindo@btcctb.org',
            'ismael.bouda@btcctb.org',
            'delwende.ilboudo@btcctb.org',
            'vincent.ilboudo@btcctb.org',
            'gedeon.ouedragogo@btcctb.org',
            'samia.djeddi@btcctb.org',
            'hamza.lahmer@btcctb.org',
            'said.nefil@btcctb.org',
            'noureddine.hamousaid@btcctb.org',
            'abderezak.hamousaid@btcctb.org',
            'younes.belhafsi@btcctb.org',
            'allal.cheriftouil@btcctb.org',
            'yacine.fennour@btcctb.org',
            'ali.agha@btcctb.org',
            'maha.nashaween@btcctb.org',
            'carlos.houana@btcctb.org',
            'lucas.zimba@btcctb.org',
            'anthony.okia@btcctb.org',
            'deo.sunday@btcctb.org',
            'charles.ndizeye@btcctb.org',
            'augustine.satya@btcctb.org',
            'john.ekwang@btcctb.org',
            'abbas.mukwana@btcctb.org',
            'fred.muyanja@btcctb.org',
            'jessica.ngabire@btcctb.org',
            'george.rubongoya@btcctb.org',
            'bulaimu.mugoya@btcctb.org',
            'charles.mwesige@btcctb.org',
            'john.oloo@btcctb.org',
            'grant.zairezaiko@btcctb.org',
            'pulisi.kasolo@btcctb.org',
            'twaha.kazibwe@btcctb.org',
            'gideon.tumwizere@btcctb.org',
            'christopher.obong@btcctb.org',
            'william.omuria@btcctb.org',
            'solomon.kalyango@btcctb.org',
            'jovin.opoka@btcctb.org',
            'moses.othieno@btcctb.org',
            'abdou.sylla@btcctb.org',
            'abdoulaye.goudiaby@btcctb.org',
            'abdoulaziz.ndiaye@btcctb.org',
            'abou.dieng@btcctb.org',
            'aboubakari.taro@btcctb.org',
            'aboudou.salifou@btcctb.org',
            'adelard.tshiamundelezola@btcctb.org',
            'adrien.lanlenou@btcctb.org',
            'alain.kambalakaninda@btcctb.org',
            'alain.kwenge@btcctb.org',
            'albert.kamonakishala@btcctb.org',
            'alexis.mbweshikuongo@btcctb.org',
            'alfred.kindulu@btcctb.org',
            'alia.diop@btcctb.org',
            'aloys.ekilimboyo@btcctb.org',
            'amadou.deme@btcctb.org',
            'amadou.fall@btcctb.org',
            'amadou.sow@btcctb.org',
            'amady.ka@btcctb.org',
            'ameth.ba@btcctb.org',
            'anderson.ngiamakaleka@btcctb.org',
            'andre.kitambalaomana@btcctb.org',
            'andre.koumassegbo@btcctb.org',
            'anicet.bukwebisibisi@btcctb.org',
            'armand.ekambi@btcctb.org',
            'arsene.matuludiyoka@btcctb.org',
            'aspasie.kpakpo@btcctb.org',
            'assane.ndiaye@btcctb.org',
            'audrey.adjovi@btcctb.org',
            'augustin.masikinidanyo@btcctb.org',
            'augustin.masimangoaluta@btcctb.org',
            'banigbe.dahoui@btcctb.org',
            'basile.kinnou@btcctb.org',
            'baudouin.tabukanga@btcctb.org',
            'bibiche.alulubatilaelo@btcctb.org',
            'bibiche.yasapkwagegito@btcctb.org',
            'bintiwa.johnsonayih-yenu@btcctb.org',
            'birame.diouf@btcctb.org',
            'bourama.badji@btcctb.org',
            'carine.bwalyamwape@btcctb.org',
            'carol.amouro@btcctb.org',
            'cartier.mulangutshibanda@btcctb.org',
            'catherine.gouhizoun@btcctb.org',
            'claude.ngomamuanda@btcctb.org',
            'claudine.kuseyondwili@btcctb.org',
            'clotilde.nsekilutonadio@btcctb.org',
            'comlan.lossou@btcctb.org',
            'constant.loko@btcctb.org',
            'cossi.ahoussou@btcctb.org',
            'coumack.thioune@btcctb.org',
            'delphin.pembaetoy@btcctb.org',
            'denis.kalambayinyamabo@btcctb.org',
            'desire.batubengakatumba@btcctb.org',
            'dieudonne.makasi@btcctb.org',
            'dieudonne.makasikikeka@btcctb.org',
            'dieudonne.ompembeipanga@btcctb.org',
            'dine.elhadjseni@btcctb.org',
            'dominique.kadimamuya@btcctb.org',
            'edmond.hounye@btcctb.org',
            'elhadjimalick.ndiaye@btcctb.org',
            'eric.meffon@btcctb.org',
            'etienne.azogo@btcctb.org',
            'euloge.daga@btcctb.org',
            'evariste.dossou@btcctb.org',
            'felix.itamponikoko@btcctb.org',
            'flavien.ahanhanzoglele@btcctb.org',
            'florent.yumamusafiri@btcctb.org',
            'florentin.bahanuzi@btcctb.org',
            'fortune.hossou@btcctb.org',
            'francoise.kapamvule@btcctb.org',
            'freddy.matungulumukanza@btcctb.org',
            'gaetan.mechede@btcctb.org',
            'gerard.bafuka@btcctb.org',
            'germain.owolabi@btcctb.org',
            'gilles.lanyan@btcctb.org',
            'gladys.coulon@btcctb.org',
            'guillaume.ahovissi@btcctb.org',
            'guy.balabalakahuaba@btcctb.org',
            'guy.manwana@btcctb.org',
            'henri.mwanansukaapiung@btcctb.org',
            'hippplyte.ngoiekalala@btcctb.org',
            'ibrahim.mbumbamavinga@btcctb.org',
            'ibrahima.diawara@btcctb.org',
            'inno.mesakavunzangandu@btcctb.org',
            'ismaila.diabyndiaye@btcctb.org',
            'jacques.dake@btcctb.org',
            'jean-baptiste.voitan@btcctb.org',
            'jean-baptste.mondondimopotu@btcctb.org',
            'jean-claude.mbayajadika@btcctb.org',
            'jean-jacques.kabanga@btcctb.org',
            'jean-jacques.matadiwanga@btcctb.org',
            'jean-jacques.somwemudimbi@btcctb.org',
            'jean-marie.matungululusanga@btcctb.org',
            'jean-paul.monteiro@btcctb.org',
            'jean-pierre.sero@btcctb.org',
            'jeanlouis.lundumumbala@btcctb.org',
            'jeanpaul.ayurambibaswa@btcctb.org',
            'jeanpaul.kabwekazadi@btcctb.org',
            'jerome.kakpo@btcctb.org',
            'jerome.mpo@btcctb.org',
            'jhon.lowalambongo@btcctb.org',
            'jocelyne.olodo@btcctb.org',
            'jonas.kuivon@btcctb.org',
            'joseph.tossavi@btcctb.org',
            'judicael.agonmazinsou@btcctb.org',
            'jules.kandosimbim@btcctb.org',
            'justin.dakou@btcctb.org',
            'khadidiatou.dia@btcctb.org',
            'komlan.sobakin@btcctb.org',
            'leandre.houeto@btcctb.org',
            'leon.ntambwanshimba@btcctb.org',
            'loretta.lukoki@btcctb.org',
            'mamadou.kane@btcctb.org',
            'mamadou.salifouabi@btcctb.org',
            'mameseyni.dione@btcctb.org',
            'mao.lutumbakanangila@btcctb.org',
            'marie.akakpodoubogan@btcctb.org',
            'mariettei.ogoukoffi@btcctb.org',
            'marthe.gnide@btcctb.org',
            'martin.guidan@btcctb.org',
            'matthieu.mokulamokili@btcctb.org',
            'matthieu.yakusuledi@btcctb.org',
            'maurice.ganmavo@btcctb.org',
            'maurille.sohantode@btcctb.org',
            'mendel.soglohoun@btcctb.org',
            'mere.banda@btcctb.org',
            'mouhamadoubamba.niang@btcctb.org',
            'nathaniel.samey@btcctb.org',
            'noel.aongaketukuhwino@btcctb.org',
            'noellah.arhalimba@btcctb.org',
            'ntcha.nouemou@btcctb.org',
            'omer.mbuyambatshibangu@btcctb.org',
            'orlan.mfingulu@btcctb.org',
            'ousmane.fall@btcctb.org',
            'ousseynou.diagne@btcctb.org',
            'pamphile.adandotokpa@btcctb.org',
            'papa.gaye@btcctb.org',
            'papaoumar.camara@btcctb.org',
            'papy.luzolongangu@btcctb.org',
            'pascal.edoun@btcctb.org',
            'pasteur.kashalakolala@btcctb.org',
            'patchely.ndangimuanda@btcctb.org',
            'patient.houenha@btcctb.org',
            'patrick.mwamba@btcctb.org',
            'penriyo.sinakouarou@btcctb.org',
            'petia.ilieva@btcctb.org',
            'petronie.mpongokanyeba@btcctb.org',
            'pierrette.akpi@btcctb.org',
            'platini.mayindombemoke@btcctb.org',
            'pontien.tshibangumukubayi@btcctb.org',
            'raissa.kperou@btcctb.org',
            'raissatou.ango@btcctb.org',
            'rami.aledji@btcctb.org',
            'rene.pare@btcctb.org',
            'richard.amoussou@btcctb.org',
            'richard.bonyakambomusalambo@btcctb.org',
            'robert.mingwenetshienabe@btcctb.org',
            'samuel.luambanzuzi@btcctb.org',
            'samuel.nsimba@btcctb.org',
            'sandrine.sossou@btcctb.org',
            'senami.nadegeallabi@btcctb.org',
            'serigne.gueye@btcctb.org',
            'severin.finfieki@btcctb.org',
            'sikirou.oloulotan@btcctb.org',
            'simplice.gohoungo@btcctb.org',
            'sotima.tamoute@btcctb.org',
            'souleymane.ndiaye@btcctb.org',
            'stephane.kadimashambuyi@btcctb.org',
            'sylvestre.akpassonou@btcctb.org',
            'sylvie.kelakabongo@btcctb.org',
            'tadognon.hounouvi@btcctb.org',
            'theodore.hountondji@btcctb.org',
            'therese.lusamba@btcctb.org',
            'thomas.sagui@btcctb.org',
            'timothee.mukendi@btcctb.org',
            'valentin.ndjibumalangu@btcctb.org',
            'victo.dagnito@btcctb.org',
            'victor.yeropa@btcctb.org',
            'vincent.ntwalantambu@btcctb.org',
            'virginie.assogba@btcctb.org',
            'waliou.tidjani@btcctb.org',
            'willy.kambambakipanga@btcctb.org',
            'willy.lohandjolamuhindi@btcctb.org',
            'winy-luta.atosa@btcctb.org',
            'yacoubou.zakariallou@btcctb.org',
            'yvon.gbaguidi@btcctb.org',
            'honore.cilumba@btcctb.org',
            'jules.batossi@btcctb.org',
            'alain.kwengemayikondo@btcctb.org',
            'ngnonnisse.tossou@btcctb.org',
            'cheick.ouedraogo@btcctb.org',
            'graziella.ghesquiere@btcctb.org',
            'constant.loko@btcctb.org',
            'jules.batossi@btcctb.org',
            'richard.amoussou@btcctb.org',
            'thomas.sagui@btcctb.org',
            'justin.dakou@btcctb.org',
            'thiam.seyni@btcctb.org',
            'ibrahim.alzouma@btcctb.org',
            'salifou.hama@btcctb.org',
            'kimba.adamou@btcctb.org',
            'idani.djibrilla@btcctb.org',
            'ali.mamoudou@btcctb.org',
            'hassoumi.ousmane@btcctb.org',
            'souleymane.boubacar@btcctb.org',
            'habsatou.moussamohamed@btcctb.org',
            'djidou.amadou@btcctb.org',
            'abdoulaye.himou@btcctb.org',
            'diabrilla.yonaba@btcctb.org',
            'moussa.hamadou@btcctb.org',
            'hassan.isidore@btcctb.org',
            'mouhamadaou.tanko@btcctb.org',
            'baripougouni.lankoande@btcctb.org',
            'ali.yoni@btcctb.org',
            'fiona.ghumpi@btcctb.org',
            'marius.ndondo@btcctb.org',
            'danny.hosea@btcctb.org',
            'salum.mdula@btcctb.org',
            'adam.apolinary@btcctb.org',
            'calist.matei@btcctb.org',
            'wilson.sedekia@btcctb.org',
            'allenlay.kalangu@btcctb.org',
            'aurencia.pamba@btcctb.org',
            'peter.msolwa@btcctb.org',
            'peter.lungu@btcctb.org',
            'edwin.jonathan@btcctb.org',
            'james.rutta@btcctb.org',
            'ferdinand.uronu@btcctb.org',
            'wilson.machumu@btcctb.org',
            'simon.mpunga@btcctb.org',
            'mamadoumouctar.barry@btcctb.org',
            'jeanpaulin.sonomou@btcctb.org',
            'mamadoulamarana.guisse@btcctb.org',
            'mamadousaidou.diallo@btcctb.org',
            'sekou.mansare@btcctb.org',
            'mamadama.bangoura@btcctb.org',
            'mamadou.gueye@btcctb.org',
            'mamadousalioub.diallo@btcctb.org',
            'mamadousaliou.diallo@btcctb.org',
            'mohamednazim.agmattahel@btcctb.org',
            'mahamadou.bah@btcctb.org',
            'issa.cisse@btcctb.org',
            'aboubacar.coulibaly@btcctb.org',
            'daouda.kante@btcctb.org',
            'amadou.kodio@btcctb.org',
            'mamadou.koumare@btcctb.org',
            'hawa.sissoko@btcctb.org',
            'aminata.koumare@btcctb.org',
            'amadou.tall@btcctb.org',
            'moussa.tienta@btcctb.org',
            'sega.diallo@btcctb.org',
            'elhadji.doucoure@btcctb.org',
            'moussa.alassane@btcctb.org',
            'ibrahim.cisse@btcctb.org',
            'karen.garcia@btcctb.org',
            'carlos.montoya@btcctb.org',
        ];
    }

}
