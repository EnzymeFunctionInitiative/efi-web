<?php
require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

$extraStyle = '<link rel="stylesheet" type="text/css" href="css/refs.css">';
if (isset($StyleAdditional))
    array_push($StyleAdditional, $extraStyle);
else
    $StyleAdditional = array($extraStyle);
$NoAdmin = true;
$ExtraTitle = "Publications";
require_once("inc/header.inc.php");

$has_advanced_options = global_settings::advanced_options_enabled();

?>

<?php
$active_tab = "pubs";
include("inc/tab_header.inc.php");
?>
<div id="pubs" class="<?php echo $tab_class; ?>">

<h3>5 US Patents</h3>
<div class="ref-group">
    <div class="ref-index">1.</div>
    <div class="ref-body">
    <span class="ref-author">US2019/0144798A1</span>,
    <span class="ref-title">Cleaning  composition</span>.
    <span class="ref-pub">The Procter & Gamble Company</span>,
    <span class="ref-year">Cincinnati, OH</span>.
    <span class="ref-year">May 16, 2019</span>.
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">2.</div>
    <div class="ref-body">
    <span class="ref-author">US2019/0144801</span>,
    <span class="ref-title">Cleaning  composition</span>.
    <span class="ref-pub">The Procter & Gamble Company</span>,
    <span class="ref-year">Cincinnati, OH</span>.
    <span class="ref-year">May 16, 2019</span>.
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">3.</div>
    <div class="ref-body">
    <span class="ref-author">US2019/0194599A1</span>,
    <span class="ref-title">Methods for generating a bacterial hemoglobin library and uses thereof</span>.
    <span class="ref-pub">Zymergen, Inc.</span>,
    <span class="ref-year">Emeryville, CA</span>.
    <span class="ref-year">June 27, 2019</span>.
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">4.</div>
    <div class="ref-body">
    <span class="ref-author">US2019/0209625A1</span>,
    <span class="ref-title">Engineering therapeutic probiotic system and method</span>.
    <span class="ref-pub">National University of Singapore</span>,
    <span class="ref-year">Singapore</span>.
    <span class="ref-year">July 11, 2019</span>.
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">5.</div>
    <div class="ref-body">
    <span class="ref-author">US2019/0225663A1</span>,
    <span class="ref-title">Discovery of cationic nonribosomal peptides as gram-negative antibiotics through global genome mining</span>.
    <span class="ref-pub">Hong Kong University of Science and Technology</span>,
    <span class="ref-year">China Ocean Mineral Resources R&D Association, Hong Kong CN</span>.
    <span class="ref-year">July 25, 2019</span>.
    </div>
</div>
<h3>288 Journal Articles</h3>
<div class="ref-group">
    <div class="ref-index">1.</div>
    <div class="ref-body">
    <span class="ref-author">Dunbar, K.L., J.R. Chekan, C.L. Cox, B.J. Burkhart, S.K. Nair, D.A. Mitchell</span>,
    <span class="ref-title">Discovery of a new ATP-binding motif involved in peptidic azoline biosynthesis</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2014</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">823&ndash;829</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchembio.1608">http://doi.org/10.1038/nchembio.1608</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">2.</div>
    <div class="ref-body">
    <span class="ref-author">Wichelecki, D.J., B.M. Balthazor, A.C. Chau, M.W. Vetting, A.A. Fedorov, E.V. Fedorov, T. Lukk, Y.V. Patskovsky, M.B. Stead, B.S. Hillerich, R.D. Seidel, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">Discovery of Function in the Enolase Superfamily: <small>D</small>-Mannonate and <small>D</small>-Gluconate Dehydratases in the <small>D</small>-Mannonate Dehydratase Subgroup</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2014</span>.
    <span class="ref-volume">53</span>(<span class="ref-number">16</span>):    p. <span class="ref-page">2722&ndash;2731</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/bi500264p">http://doi.org/10.1021/bi500264p</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">3.</div>
    <div class="ref-body">
    <span class="ref-author">Wichelecki, D.J., D.C. Graff, N. Al-Obaidi, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">Identification of the in Vivo Function of the High-Efficiency <small>D</small>-Mannonate Dehydratase in Caulobacter crescentus NA1000 from the Enolase Superfamily</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2014</span>.
    <span class="ref-volume">53</span>(<span class="ref-number">25</span>):    p. <span class="ref-page">4087&ndash;4089</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/bi500683x">http://doi.org/10.1021/bi500683x</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">4.</div>
    <div class="ref-body">
    <span class="ref-author">Anders, K., L.-O. Essen</span>,
    <span class="ref-title">The family of phytochrome-like photoreceptors: diverse, complex and multi-colored, but very useful</span>.
    <span class="ref-pub">Current Opinion in Structural Biology,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">35</span>:    p. <span class="ref-page">7&ndash;16</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.sbi.2015.07.005">http://doi.org/10.1016/j.sbi.2015.07.005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">5.</div>
    <div class="ref-body">
    <span class="ref-author">Burkhart, B.J., G.A. Hudson, K.L. Dunbar, D.A. Mitchell</span>,
    <span class="ref-title">A prevalent peptide-binding domain guides ribosomal natural product biosynthesis</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">564&ndash;570</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchembio.1856">http://doi.org/10.1038/nchembio.1856</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">6.</div>
    <div class="ref-body">
    <span class="ref-author">Celis, A.I., J.L. DuBois</span>,
    <span class="ref-title">Substrate, product, and cofactor: The extraordinarily flexible relationship between the CDE superfamily and heme</span>.
    <span class="ref-pub">Archives of Biochemistry and Biophysics,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">574</span>:    p. <span class="ref-page">3&ndash;17</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.abb.2015.03.004">http://doi.org/10.1016/j.abb.2015.03.004</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">7.</div>
    <div class="ref-body">
    <span class="ref-author">Colin, P.-Y., B. Kintses, F. Gielen, C.M. Miton, G. Fischer, M.F. Mohamed, M. Hyvönen, D.P. Morgavi, D.B. Janssen, F. Hollfelder</span>,
    <span class="ref-title">Ultrahigh-throughput discovery of promiscuous enzymes by picodroplet functional metagenomics</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">6</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/ncomms10008">http://doi.org/10.1038/ncomms10008</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">8.</div>
    <div class="ref-body">
    <span class="ref-author">Cox, C.L., J.R. Doroghazi, D.A. Mitchell</span>,
    <span class="ref-title">The genomic landscape of ribosomal peptides containing thiazole and oxazole heterocycles</span>.
    <span class="ref-pub">BMC Genomics,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">16</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s12864-015-2008-0">http://doi.org/10.1186/s12864-015-2008-0</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">9.</div>
    <div class="ref-body">
    <span class="ref-author">Huang, H., M.S. Carter, M.W. Vetting, N. Al-Obaidi, Y. Patskovsky, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">A General Strategy for the Discovery of Metabolic Pathways: <small>D</small>-Threitol, <small>L</small>-Threitol, and Erythritol Utilization in Mycobacterium smegmatis</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">137</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">14570&ndash;14573</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.5b08968">http://doi.org/10.1021/jacs.5b08968</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">10.</div>
    <div class="ref-body">
    <span class="ref-author">Latham, J.A., A.T. Iavarone, I. Barr, P.V. Juthani, J.P. Klinman</span>,
    <span class="ref-title">PqqD Is a Novel Peptide Chaperone That Forms a Ternary Complex with the Radical <i>S</i>-Adenosylmethionine Protein PqqE in the Pyrroloquinoline Quinone Biosynthetic Pathway</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">290</span>(<span class="ref-number">20</span>):    p. <span class="ref-page">12908&ndash;12918</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m115.646521">http://doi.org/10.1074/jbc.m115.646521</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">11.</div>
    <div class="ref-body">
    <span class="ref-author">Liu, F., J. Geng, R.H. Gumpper, A. Barman, I. Davis, A. Ozarowski, D. Hamelberg, A. Liu</span>,
    <span class="ref-title">An Iron Reservoir to the Catalytic Metal</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">290</span>(<span class="ref-number">25</span>):    p. <span class="ref-page">15621&ndash;15634</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m115.650259">http://doi.org/10.1074/jbc.m115.650259</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">12.</div>
    <div class="ref-body">
    <span class="ref-author">Petronikolou, N., S.K. Nair</span>,
    <span class="ref-title">Biochemical Studies of Mycobacterial Fatty Acid Methyltransferase: A Catalyst for the Enzymatic Production of Biodiesel</span>.
    <span class="ref-pub">Chemistry & Biology,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">22</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">1480&ndash;1490</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.chembiol.2015.09.011">http://doi.org/10.1016/j.chembiol.2015.09.011</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">13.</div>
    <div class="ref-body">
    <span class="ref-author">Rao, G., B. O'Dowd, J. Li, K. Wang, E. Oldfield</span>,
    <span class="ref-title">IspH&ndash;RPS1 and IspH&ndash;UbiA: "Rosetta stone" proteins</span>.
    <span class="ref-pub">Chemical Science,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">6</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">6813&ndash;6822</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c5sc02600h">http://doi.org/10.1039/c5sc02600h</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">14.</div>
    <div class="ref-body">
    <span class="ref-author">Roche, D., D. Brackenridge, L. McGuffin</span>,
    <span class="ref-title">Proteins and Their Interacting Partners: An Introduction to Protein&ndash;Ligand Binding Site Prediction Methods</span>.
    <span class="ref-pub">International Journal of Molecular Sciences,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">16</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">29829&ndash;29842</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/ijms161226202">http://doi.org/10.3390/ijms161226202</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">15.</div>
    <div class="ref-body">
    <span class="ref-author">San Francisco, B., X. Zhang, K. Whalen, J. Gerlt</span>,
    <span class="ref-title">A Novel Pathway for Bacterial Ethanolamine Metabolism</span>.
    <span class="ref-pub">The FASEB Journal,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">29</span>(<span class="ref-number">1_supplement</span>):    p. <span class="ref-page">573.45</span>.    </div>
</div>
<div class="ref-group">
    <div class="ref-index">16.</div>
    <div class="ref-body">
    <span class="ref-author">Vetting, M.W., N. Al-Obaidi, S. Zhao, B. San Francisco, J. Kim, D.J. Wichelecki, J.T. Bouvier, J.O. Solbiati, H. Vu, X. Zhang, D.A. Rodionov, J.D. Love, B.S. Hillerich, R.D. Seidel, R.J. Quinn, A.L. Osterman, J.E. Cronan, M.P. Jacobson, J.A. Gerlt, S.C. Almo</span>,
    <span class="ref-title">Experimental Strategies for Functional Annotation and Metabolism Discovery: Targeted Screening of Solute Binding Proteins and Unbiased Panning of Metabolomes</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">54</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">909&ndash;931</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/bi501388y">http://doi.org/10.1021/bi501388y</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">17.</div>
    <div class="ref-body">
    <span class="ref-author">Wichelecki, D.J., M.W. Vetting, L. Chou, N. Al-Obaidi, J.T. Bouvier, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">ATP-binding Cassette (ABC) Transport System Solute-binding Protein-guided Identification of Novel <small>D</small>-Altritol and Galactitol Catabolic Pathways in Agrobacterium tumefaciens C58</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">290</span>(<span class="ref-number">48</span>):    p. <span class="ref-page">28963&ndash;28976</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m115.686857">http://doi.org/10.1074/jbc.m115.686857</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">18.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, X., R. Kumar, M.W. Vetting, S. Zhao, M.P. Jacobson, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">A Unique cis-3-Hydroxy-<small>L</small>-proline Dehydratase in the Enolase Superfamily</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2015</span>.
    <span class="ref-volume">137</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">1388&ndash;1391</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/ja5103986">http://doi.org/10.1021/ja5103986</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">19.</div>
    <div class="ref-body">
    <span class="ref-author">Ahmed, F.H., A.E. Mohamed, P.D. Carr, B.M. Lee, K. Condic-Jurkic, M.L. O'Mara, C.J. Jackson</span>,
    <span class="ref-title">Rv2074 is a novel F420H2-dependent biliverdin reductase in Mycobacterium tuberculosis</span>.
    <span class="ref-pub">Protein Science,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">25</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1692&ndash;1709</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/pro.2975">http://doi.org/10.1002/pro.2975</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">20.</div>
    <div class="ref-body">
    <span class="ref-author">Atkinson, J.T., I. Campbell, G.N. Bennett, J.J. Silberg</span>,
    <span class="ref-title">Cellular Assays for Ferredoxins: A Strategy for Understanding Electron Flow through Protein Carriers That Link Metabolic Pathways</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">55</span>(<span class="ref-number">51</span>):    p. <span class="ref-page">7047&ndash;7064</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.6b00831">http://doi.org/10.1021/acs.biochem.6b00831</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">21.</div>
    <div class="ref-body">
    <span class="ref-author">Baier, F., J.N. Copp, N. Tokuriki</span>,
    <span class="ref-title">Evolution of Enzyme Superfamilies: Comprehensive Exploration of Sequence&ndash;Function Relationships</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">55</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">6375&ndash;6388</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.6b00723">http://doi.org/10.1021/acs.biochem.6b00723</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">22.</div>
    <div class="ref-body">
    <span class="ref-author">Bhandari, D.M., D. Fedoseyenko, T.P. Begley</span>,
    <span class="ref-title">Tryptophan Lyase (NosL): A Cornucopia of 5&prime;-Deoxyadenosyl Radical Mediated Transformations</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">138</span>(<span class="ref-number">50</span>):    p. <span class="ref-page">16184&ndash;16187</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.6b06139">http://doi.org/10.1021/jacs.6b06139</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">23.</div>
    <div class="ref-body">
    <span class="ref-author">Chekan, J.R., J.D. Koos, C. Zong, M.O. Maksimov, A.J. Link, S.K. Nair</span>,
    <span class="ref-title">Structure of the Lasso Peptide Isopeptidase Identifies a Topology for Processing Threaded Substrates</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">138</span>(<span class="ref-number">50</span>):    p. <span class="ref-page">16452&ndash;16458</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.6b10389">http://doi.org/10.1021/jacs.6b10389</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">24.</div>
    <div class="ref-body">
    <span class="ref-author">Colabroy, K.L.</span>,
    <span class="ref-title">Tearing down to build up: Metalloenzymes in the biosynthesis lincomycin, hormaomycin and the pyrrolo [1,4]benzodiazepines</span>.
    <span class="ref-pub">Biochimica et Biophysica Acta (BBA) - Proteins and Proteomics,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">1864</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">724&ndash;737</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbapap.2016.03.001">http://doi.org/10.1016/j.bbapap.2016.03.001</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">25.</div>
    <div class="ref-body">
    <span class="ref-author">Dassama, L.M.K., G.E. Kenney, S.Y. Ro, E.L. Zielazinski, A.C. Rosenzweig</span>,
    <span class="ref-title">Methanobactin transport machinery</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">113</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">13027&ndash;13032</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1603578113">http://doi.org/10.1073/pnas.1603578113</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">26.</div>
    <div class="ref-body">
    <span class="ref-author">Davey, L., S.A. Halperin, S.F. Lee</span>,
    <span class="ref-title">Thiol-Disulfide Exchange in Gram-Positive Firmicutes</span>.
    <span class="ref-pub">Trends in Microbiology,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">24</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">902&ndash;915</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.tim.2016.06.010">http://doi.org/10.1016/j.tim.2016.06.010</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">27.</div>
    <div class="ref-body">
    <span class="ref-author">Desai, J., Y.-L. Liu, H. Wei, W. Liu, T.-P. Ko, R.-T. Guo, E. Oldfield</span>,
    <span class="ref-title">Structure, Function, and Inhibition of Staphylococcus aureus Heptaprenyl Diphosphate Synthase</span>.
    <span class="ref-pub">ChemMedChem,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">17</span>):    p. <span class="ref-page">1915&ndash;1923</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cmdc.201600311">http://doi.org/10.1002/cmdc.201600311</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">28.</div>
    <div class="ref-body">
    <span class="ref-author">Ding, W., Q. Li, Y. Jia, X. Ji, H. Qianzhu, Q. Zhang</span>,
    <span class="ref-title">Emerging Diversity of the Cobalamin-Dependent Methyltransferases Involving Radical-Based Mechanisms</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">13</span>):    p. <span class="ref-page">1191&ndash;1197</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201600107">http://doi.org/10.1002/cbic.201600107</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">29.</div>
    <div class="ref-body">
    <span class="ref-author">Gerlt, J.A.</span>,
    <span class="ref-title">Tools and strategies for discovering novel enzymes and metabolic pathways</span>.
    <span class="ref-pub">Perspectives in Science,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">9</span>:    p. <span class="ref-page">24&ndash;32</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.pisc.2016.07.001">http://doi.org/10.1016/j.pisc.2016.07.001</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">30.</div>
    <div class="ref-body">
    <span class="ref-author">Ghodge, S.V., K.A. Biernat, S.J. Bassett, M.R. Redinbo, A.A. Bowers</span>,
    <span class="ref-title">Post-translational Claisen Condensation and Decarboxylation en Route to the Bicyclic Core of Pantocin A</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">138</span>(<span class="ref-number">17</span>):    p. <span class="ref-page">5487&ndash;5490</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.5b13529">http://doi.org/10.1021/jacs.5b13529</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">31.</div>
    <div class="ref-body">
    <span class="ref-author">Hao, Y., E. Pierce, D. Roe, M. Morita, J.A. McIntosh, V. Agarwal, T.E. Cheatham, E.W. Schmidt, S.K. Nair</span>,
    <span class="ref-title">Molecular basis for the broad substrate selectivity of a peptide prenyltransferase</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">113</span>(<span class="ref-number">49</span>):    p. <span class="ref-page">14037&ndash;14042</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1609869113">http://doi.org/10.1073/pnas.1609869113</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">32.</div>
    <div class="ref-body">
    <span class="ref-author">Tietz, J.I., D.A. Mitchell</span>,
    <span class="ref-title">Using Genomics for Natural Product Structure Elucidation</span>.
    <span class="ref-pub">Current Topics in Medicinal Chemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">16</span>(<span class="ref-number">15</span>):    p. <span class="ref-page">1645&ndash;1694</span>.    <span class="ref-url"><a href="http://doi.org/10.2174/1568026616666151012111439">http://doi.org/10.2174/1568026616666151012111439</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">33.</div>
    <div class="ref-body">
    <span class="ref-author">Ji, X., Y. Li, L. Xie, H. Lu, W. Ding, Q. Zhang</span>,
    <span class="ref-title">Expanding Radical SAM Chemistry by Using Radical Addition Reactions and SAM Analogues</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">55</span>(<span class="ref-number">39</span>):    p. <span class="ref-page">11845&ndash;11848</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201605917">http://doi.org/10.1002/anie.201605917</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">34.</div>
    <div class="ref-body">
    <span class="ref-author">Ji, X., W.-Q. Liu, S. Yuan, Y. Yin, W. Ding, Q. Zhang</span>,
    <span class="ref-title">Mechanistic study of the radical SAM-dependent amine dehydrogenation reactions</span>.
    <span class="ref-pub">Chemical Communications,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">52</span>(<span class="ref-number">69</span>):    p. <span class="ref-page">10555&ndash;10558</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c6cc05661j">http://doi.org/10.1039/c6cc05661j</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">35.</div>
    <div class="ref-body">
    <span class="ref-author">Kumar, G., J.L. Johnson, P.A. Frantom</span>,
    <span class="ref-title">Improving Functional Annotation in the DRE-TIM Metallolyase Superfamily through Identification of Active Site Fingerprints</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">55</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">1863&ndash;1872</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.5b01193">http://doi.org/10.1021/acs.biochem.5b01193</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">36.</div>
    <div class="ref-body">
    <span class="ref-author">Li, D., R. Moorman, T. Vanhercke, J. Petrie, S. Singh, C.J. Jackson</span>,
    <span class="ref-title">Classification and substrate head-group specificity of membrane fatty acid desaturases</span>.
    <span class="ref-pub">Computational and Structural Biotechnology Journal,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">14</span>:    p. <span class="ref-page">341&ndash;349</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.csbj.2016.08.003">http://doi.org/10.1016/j.csbj.2016.08.003</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">37.</div>
    <div class="ref-body">
    <span class="ref-author">Machovina, M.M., R.J. Usselman, J.L. DuBois</span>,
    <span class="ref-title">Monooxygenase Substrates Mimic Flavin to Catalyze Cofactorless Oxygenations</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">291</span>(<span class="ref-number">34</span>):    p. <span class="ref-page">17816&ndash;17828</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m116.730051">http://doi.org/10.1074/jbc.m116.730051</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">38.</div>
    <div class="ref-body">
    <span class="ref-author">Maxson, T., J.I. Tietz, G.A. Hudson, X.R. Guo, H.-C. Tai, D.A. Mitchell</span>,
    <span class="ref-title">Targeting Reactive Carbonyls for Identifying Natural Products and Their Biosynthetic Origins</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">138</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">15157&ndash;15166</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.6b06848">http://doi.org/10.1021/jacs.6b06848</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">39.</div>
    <div class="ref-body">
    <span class="ref-author">Molloy, E.M., J.I. Tietz, P.M. Blair, D.A. Mitchell</span>,
    <span class="ref-title">Biological characterization of the hygrobafilomycin antibiotic JBIR-100 and bioinformatic insights into the hygrolide family of natural products</span>.
    <span class="ref-pub">Bioorganic & Medicinal Chemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">24</span>(<span class="ref-number">24</span>):    p. <span class="ref-page">6276&ndash;6290</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bmc.2016.05.021">http://doi.org/10.1016/j.bmc.2016.05.021</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">40.</div>
    <div class="ref-body">
    <span class="ref-author">Plach, M.G., B. Reisinger, R. Sterner, R. Merkl</span>,
    <span class="ref-title">Long-Term Persistence of Bi-functionality Contributes to the Robustness of Microbial Life through Exaptation</span>.
    <span class="ref-pub">PLOS Genetics,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">e1005836</span>.    <span class="ref-url"><a href="http://doi.org/10.1371/journal.pgen.1005836">http://doi.org/10.1371/journal.pgen.1005836</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">41.</div>
    <div class="ref-body">
    <span class="ref-author">Prunetti, L., B.E. Yacoubi, C.R. Schiavon, E. Kirkpatrick, L. Huang, M. Bailly, M.E. Badawi-Sidhu, K. Harrison, J.F. Gregory, O. Fiehn, A.D. Hanson, V. de Cr&eacute;cy-Lagard</span>,
    <span class="ref-title">Evidence that COG0325 proteins are involved in PLP homeostasis</span>.
    <span class="ref-pub">Microbiology,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">162</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">694&ndash;706</span>.    <span class="ref-url"><a href="http://doi.org/10.1099/mic.0.000255">http://doi.org/10.1099/mic.0.000255</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">42.</div>
    <div class="ref-body">
    <span class="ref-author">Rao, G., E. Oldfield</span>,
    <span class="ref-title">Structure and Function of Four Classes of the 4Fe&ndash;4S Protein, IspH</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">55</span>(<span class="ref-number">29</span>):    p. <span class="ref-page">4119&ndash;4129</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.6b00474">http://doi.org/10.1021/acs.biochem.6b00474</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">43.</div>
    <div class="ref-body">
    <span class="ref-author">Thotsaporn, K., R. Tinikul, S. Maenpuen, J. Phonbuppha, P. Watthaisong, P. Chenprakhon, P. Chaiyen</span>,
    <span class="ref-title">Enzymes in the p-hydroxyphenylacetate degradation pathway of Acinetobacter baumannii</span>.
    <span class="ref-pub">Journal of Molecular Catalysis B: Enzymatic,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">134</span>:    p. <span class="ref-page">353&ndash;366</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.molcatb.2016.09.003">http://doi.org/10.1016/j.molcatb.2016.09.003</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">44.</div>
    <div class="ref-body">
    <span class="ref-author">Zallot, R., K. Harrison, B. Kolaczkowski, V. de Cr&eacute;cy-Lagard</span>,
    <span class="ref-title">Functional Annotations of Paralogs: A Blessing and a Curse</span>.
    <span class="ref-pub">Life,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">6</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">39</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/life6030039">http://doi.org/10.3390/life6030039</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">45.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, X., M.S. Carter, M.W. Vetting, B. San Francisco, S. Zhao, N.F. Al-Obaidi, J.O. Solbiati, J.J. Thiaville, V. de Cr&eacute;cy-Lagard, M.P. Jacobson, S.C. Almo, J.A. Gerlt</span>,
    <span class="ref-title">Assignment of function to a domain of unknown function: DUF1537 is a new kinase family in catabolic pathways for acid sugars</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">113</span>(<span class="ref-number">29</span>):    p. <span class="ref-page">E4161&ndash;E4169</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1605546113">http://doi.org/10.1073/pnas.1605546113</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">46.</div>
    <div class="ref-body">
    <span class="ref-author">Ahmed, M.N., E. Reyna-Gonz&aacute;lez, B. Schmid, V. Wiebach, R.D. Süssmuth, E. Dittmann, D.P. Fewer</span>,
    <span class="ref-title">Phylogenomic Analysis of the Microviridin Biosynthetic Pathway Coupled with Targeted Chemo-Enzymatic Synthesis Yields Potent Protease Inhibitors</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">1538&ndash;1546</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.7b00124">http://doi.org/10.1021/acschembio.7b00124</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">47.</div>
    <div class="ref-body">
    <span class="ref-author">Bearne, S.L.</span>,
    <span class="ref-title">The interdigitating loop of the enolase superfamily as a specificity binding determinant or `flying buttress'</span>.
    <span class="ref-pub">Biochimica et Biophysica Acta (BBA) - Proteins and Proteomics,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">1865</span>(<span class="ref-number">5</span>):    p. <span class="ref-page">619&ndash;630</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbapap.2017.02.006">http://doi.org/10.1016/j.bbapap.2017.02.006</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">48.</div>
    <div class="ref-body">
    <span class="ref-author">Benjdia, A., A. Guillot, P. Ruffi&eacute;, J. Leprince, O. Berteau</span>,
    <span class="ref-title">Post-translational modification of ribosomally synthesized peptides by a radical SAM epimerase in Bacillus subtilis</span>.
    <span class="ref-pub">Nature Chemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">698&ndash;707</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchem.2714">http://doi.org/10.1038/nchem.2714</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">49.</div>
    <div class="ref-body">
    <span class="ref-author">Blin, K., T. Wolf, M.G. Chevrette, X. Lu, C.J. Schwalen, S.A. Kautsar, H.G.S. Duran, E.L.C. de los Santos, H.U. Kim, M. Nave, J.S. Dickschat, D.A. Mitchell, E. Shelest, R. Breitling, E. Takano, S.Y. Lee, T. Weber, M.H. Medema</span>,
    <span class="ref-title">antiSMASH 4.0&mdash;improvements in chemistry prediction and gene cluster boundary identification</span>.
    <span class="ref-pub">Nucleic Acids Research,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">45</span>(<span class="ref-number">W1</span>):    p. <span class="ref-page">W36&ndash;W41</span>.    <span class="ref-url"><a href="http://doi.org/10.1093/nar/gkx319">http://doi.org/10.1093/nar/gkx319</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">50.</div>
    <div class="ref-body">
    <span class="ref-author">Chowdhary, J., F.E. Löffler, J.C. Smith</span>,
    <span class="ref-title">Community detection in sequence similarity networks based on attribute clustering</span>.
    <span class="ref-pub">PLOS ONE,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">e0178650</span>.    <span class="ref-url"><a href="http://doi.org/10.1371/journal.pone.0178650">http://doi.org/10.1371/journal.pone.0178650</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">51.</div>
    <div class="ref-body">
    <span class="ref-author">Cogan, D.P., G.A. Hudson, Z. Zhang, T.V. Pogorelov, W.A. van der Donk, D.A. Mitchell, S.K. Nair</span>,
    <span class="ref-title">Structural insights into enzymatic [4+2] aza-cycloaddition in thiopeptide antibiotic biosynthesis</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">114</span>(<span class="ref-number">49</span>):    p. <span class="ref-page">12928&ndash;12933</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1716035114">http://doi.org/10.1073/pnas.1716035114</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">52.</div>
    <div class="ref-body">
    <span class="ref-author">Ding, W., W. Ji, Y. Wu, R. Wu, W.-Q. Liu, T. Mo, J. Zhao, X. Ma, W. Zhang, P. Xu, Z. Deng, B. Tang, Y. Yu, Q. Zhang</span>,
    <span class="ref-title">Biosynthesis of the nosiheptide indole side ring centers on a cryptic carrier protein NosJ</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">8</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-017-00439-1">http://doi.org/10.1038/s41467-017-00439-1</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">53.</div>
    <div class="ref-body">
    <span class="ref-author">Dong, S.-H., N.D. Frane, Q.H. Christensen, E.P. Greenberg, R. Nagarajan, S.K. Nair</span>,
    <span class="ref-title">Molecular basis for the substrate specificity of quorum signal synthases</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">114</span>(<span class="ref-number">34</span>):    p. <span class="ref-page">9092&ndash;9097</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1705400114">http://doi.org/10.1073/pnas.1705400114</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">54.</div>
    <div class="ref-body">
    <span class="ref-author">Erb, T.J., P.R. Jones, A. Bar-Even</span>,
    <span class="ref-title">Synthetic metabolism: metabolic engineering meets enzyme design</span>.
    <span class="ref-pub">Current Opinion in Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">37</span>:    p. <span class="ref-page">56&ndash;62</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.cbpa.2016.12.023">http://doi.org/10.1016/j.cbpa.2016.12.023</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">55.</div>
    <div class="ref-body">
    <span class="ref-author">Essen, L.-O., S. Franz, A. Banerjee</span>,
    <span class="ref-title">Structural and evolutionary aspects of algal blue light receptors of the cryptochrome and aureochrome type</span>.
    <span class="ref-pub">Journal of Plant Physiology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">217</span>:    p. <span class="ref-page">27&ndash;37</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.jplph.2017.07.005">http://doi.org/10.1016/j.jplph.2017.07.005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">56.</div>
    <div class="ref-body">
    <span class="ref-author">Estrada, P., M. Manandhar, S.-H. Dong, J. Deveryshetty, V. Agarwal, J.E. Cronan, S.K. Nair</span>,
    <span class="ref-title">The pimeloyl-CoA synthetase BioW defines a new fold for adenylate-forming enzymes</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">668&ndash;674</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchembio.2359">http://doi.org/10.1038/nchembio.2359</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">57.</div>
    <div class="ref-body">
    <span class="ref-author">Gerlt, J.A.</span>,
    <span class="ref-title">Genomic Enzymology: Web Tools for Leveraging Protein Family Sequence&ndash;Function Space and Genome Context to Discover Novel Functions</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">56</span>(<span class="ref-number">33</span>):    p. <span class="ref-page">4293&ndash;4308</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.7b00614">http://doi.org/10.1021/acs.biochem.7b00614</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">58.</div>
    <div class="ref-body">
    <span class="ref-author">Giessen, T.W., P.A. Silver</span>,
    <span class="ref-title">Widespread distribution of encapsulin nanocompartments reveals functional diversity</span>.
    <span class="ref-pub">Nature Microbiology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">2</span>(<span class="ref-number">6</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/nmicrobiol.2017.29">http://doi.org/10.1038/nmicrobiol.2017.29</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">59.</div>
    <div class="ref-body">
    <span class="ref-author">Glasner, M.E.</span>,
    <span class="ref-title">Finding enzymes in the gut metagenome</span>.
    <span class="ref-pub">Science,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">355</span>(<span class="ref-number">6325</span>):    p. <span class="ref-page">577&ndash;578</span>.    <span class="ref-url"><a href="http://doi.org/10.1126/science.aam7446">http://doi.org/10.1126/science.aam7446</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">60.</div>
    <div class="ref-body">
    <span class="ref-author">Grim, K.P., B. San Francisco, J.N. Radin, E.B. Brazel, J.L. Kelliher, P.K.P. Sol&oacute;rzano, P.C. Kim, C.A. McDevitt, T.E. Kehl-Fie</span>,
    <span class="ref-title">The Metallophore Staphylopine Enables Staphylococcus aureus To Compete with the Host for Zinc and Overcome Nutritional Immunity</span>.
    <span class="ref-pub">mBio,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">8</span>(<span class="ref-number">5</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/mbio.01281-17">http://doi.org/10.1128/mbio.01281-17</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">61.</div>
    <div class="ref-body">
    <span class="ref-author">Haase, E.M., Y. Kou, A. Sabharwal, Y.-C. Liao, T. Lan, C. Lindqvist, F.A. Scannapieco</span>,
    <span class="ref-title">Comparative genomics and evolution of the amylase-binding proteins of oral streptococci</span>.
    <span class="ref-pub">BMC Microbiology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s12866-017-1005-7">http://doi.org/10.1186/s12866-017-1005-7</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">62.</div>
    <div class="ref-body">
    <span class="ref-author">Hetrick, K.J., W.A. van der Donk</span>,
    <span class="ref-title">Ribosomally synthesized and post-translationally modified peptide natural product discovery in the genomic era</span>.
    <span class="ref-pub">Current Opinion in Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">38</span>:    p. <span class="ref-page">36&ndash;44</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.cbpa.2017.02.005">http://doi.org/10.1016/j.cbpa.2017.02.005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">63.</div>
    <div class="ref-body">
    <span class="ref-author">Holliday, G.L., S.D. Brown, E. Akiva, D. Mischel, M.A. Hicks, J.H. Morris, C.C. Huang, E.C. Meng, S.C. Pegg, T.E. Ferrin, P.C. Babbitt</span>,
    <span class="ref-title">Biocuration in the structure&ndash;function linkage database: the anatomy of a superfamily</span>.
    <span class="ref-pub">Database,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">2017</span>:.    <span class="ref-url"><a href="http://doi.org/10.1093/database/bax006">http://doi.org/10.1093/database/bax006</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">64.</div>
    <div class="ref-body">
    <span class="ref-author">Holliday, G.L., R. Davidson, E. Akiva, P.C. Babbitt</span>,
    <span class="ref-title">Evaluating Functional Annotations of Enzymes Using the Gene Ontology</span>.
    <span class="ref-pub">Methods in Molecular Biology,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-url"><a href="http://doi.org/10.1007/978-1-4939-3743-1_9">http://doi.org/10.1007/978-1-4939-3743-1_9</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">65.</div>
    <div class="ref-body">
    <span class="ref-author">Hopkins, D.H., N.J. Fraser, P.D. Mabbitt, P.D. Carr, J.G. Oakeshott, C.J. Jackson</span>,
    <span class="ref-title">Structure of an Insecticide Sequestering Carboxylesterase from the Disease Vector Culex quinquefasciatus: What Makes an Enzyme a Good Insecticide Sponge?</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">56</span>(<span class="ref-number">41</span>):    p. <span class="ref-page">5512&ndash;5525</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.7b00774">http://doi.org/10.1021/acs.biochem.7b00774</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">66.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., X. Jia, K.H. Kim, Z.J. Pu, M.-S. Kang, C.O. Jeon</span>,
    <span class="ref-title">Evolutionary, computational, and biochemical studies of the salicylaldehyde dehydrogenases in the naphthalene degradation pathway</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">7</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/srep43489">http://doi.org/10.1038/srep43489</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">67.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., X. Jia, K.H. Kim, C.O. Jeon</span>,
    <span class="ref-title">Integrative view of 2-oxoglutarate/Fe(II)-dependent oxygenase diversity and functions in bacteria</span>.
    <span class="ref-pub">Biochimica et Biophysica Acta (BBA) - General Subjects,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">1861</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">323&ndash;334</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbagen.2016.12.001">http://doi.org/10.1016/j.bbagen.2016.12.001</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">68.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., K. Tang, B.H. Chun, C.O. Jeon</span>,
    <span class="ref-title">Large-scale examination of functional and sequence diversity of 2-oxoglutarate/Fe(II)-dependent oxygenases in Metazoa</span>.
    <span class="ref-pub">Biochimica et Biophysica Acta (BBA) - General Subjects,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">1861</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">2922&ndash;2933</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbagen.2017.08.019">http://doi.org/10.1016/j.bbagen.2017.08.019</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">69.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., X.F. Zhu, Z.J. Pu, Y.X. Duan, L.J. Hao, J. Zhang, L.-Q. Chen, C.O. Jeon, Y.H. Xuan</span>,
    <span class="ref-title">Integrative View of the Diversity and Evolution of SWEET and SemiSWEET Sugar Transporters</span>.
    <span class="ref-pub">Frontiers in Plant Science,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">8</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fpls.2017.02178">http://doi.org/10.3389/fpls.2017.02178</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">70.</div>
    <div class="ref-body">
    <span class="ref-author">Kandlinger, F., M.G. Plach, R. Merkl</span>,
    <span class="ref-title">AGeNNT: annotation of enzyme families by means of refined neighborhood networks</span>.
    <span class="ref-pub">BMC Bioinformatics,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">18</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s12859-017-1689-6">http://doi.org/10.1186/s12859-017-1689-6</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">71.</div>
    <div class="ref-body">
    <span class="ref-author">Koppel, N., V.M. Rekdal, E.P. Balskus</span>,
    <span class="ref-title">Chemical transformation of xenobiotics by the human gut microbiota</span>.
    <span class="ref-pub">Science,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">356</span>(<span class="ref-number">6344</span>):    p. <span class="ref-page">eaag2770</span>.    <span class="ref-url"><a href="http://doi.org/10.1126/science.aag2770">http://doi.org/10.1126/science.aag2770</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">72.</div>
    <div class="ref-body">
    <span class="ref-author">Levin, B.J., Y.Y. Huang, S.C. Peck, Y. Wei, A.M. Campo, J.A. Marks, E.A. Franzosa, C. Huttenhower, E.P. Balskus</span>,
    <span class="ref-title">A prominent glycyl radical enzyme in human gut microbiomes metabolizestrans-4-hydroxy-<small>L</small>-proline</span>.
    <span class="ref-pub">Science,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">355</span>(<span class="ref-number">6325</span>):    p. <span class="ref-page">eaai8386</span>.    <span class="ref-url"><a href="http://doi.org/10.1126/science.aai8386">http://doi.org/10.1126/science.aai8386</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">73.</div>
    <div class="ref-body">
    <span class="ref-author">Liao, C., F.P. Seebeck</span>,
    <span class="ref-title">Convergent Evolution of Ergothioneine Biosynthesis in Cyanobacteria</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">18</span>(<span class="ref-number">21</span>):    p. <span class="ref-page">2115&ndash;2118</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201700354">http://doi.org/10.1002/cbic.201700354</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">74.</div>
    <div class="ref-body">
    <span class="ref-author">Lohans, C.T., D.Y. Wang, J. Wang, R.B. Hamed, C.J. Schofield</span>,
    <span class="ref-title">Crotonases: Nature's Exceedingly Convertible Catalysts</span>.
    <span class="ref-pub">ACS Catalysis,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">7</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">6587&ndash;6599</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acscatal.7b01699">http://doi.org/10.1021/acscatal.7b01699</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">75.</div>
    <div class="ref-body">
    <span class="ref-author">Macaisne, N., F. Liu, D. Scornet, A.F. Peters, A. Lipinska, M.-M. Perrineau, A. Henry, M. Strittmatter, S.M. Coelho, J.M. Cock</span>,
    <span class="ref-title">The Ectocarpus IMMEDIATE UPRIGHT gene encodes a member of a novel family of cysteine-rich proteins with an unusual distribution across the eukaryotes</span>.
    <span class="ref-pub">Development,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">144</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">409&ndash;418</span>.    <span class="ref-url"><a href="http://doi.org/10.1242/dev.141523">http://doi.org/10.1242/dev.141523</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">76.</div>
    <div class="ref-body">
    <span class="ref-author">McFarland, B.J.</span>,
    <span class="ref-title">Online Tools for Teaching Large Laboratory Courses: How the GENI Website Facilitates Authentic Research</span>.
    <span class="ref-pub">ACS Symposium Series,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-url"><a href="http://doi.org/10.1021/bk-2017-1270.ch008">http://doi.org/10.1021/bk-2017-1270.ch008</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">77.</div>
    <div class="ref-body">
    <span class="ref-author">Ney, B., F.H. Ahmed, C.R. Carere, A. Biswas, A.C. Warden, S.E. Morales, G. Pandey, S.J. Watt, J.G. Oakeshott, M.C. Taylor, M.B. Stott, C.J. Jackson, C. Greening</span>,
    <span class="ref-title">The methanogenic redox cofactor F420 is widely synthesized by aerobic soil bacteria</span>.
    <span class="ref-pub">The ISME Journal,</span>
    <span class="ref-year">2016</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">125&ndash;137</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/ismej.2016.100">http://doi.org/10.1038/ismej.2016.100</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">78.</div>
    <div class="ref-body">
    <span class="ref-author">Ortega, M.A., D.P. Cogan, S. Mukherjee, N. Garg, B. Li, G.N. Thibodeaux, S.I. Maffioli, S. Donadio, M. Sosio, J. Escano, L. Smith, S.K. Nair, W.A. van der Donk</span>,
    <span class="ref-title">Two Flavoenzymes Catalyze the Post-Translational Generation of 5-Chlorotryptophan and 2-Aminovinyl-Cysteine during NAI-107 Biosynthesis</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">548&ndash;557</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.6b01031">http://doi.org/10.1021/acschembio.6b01031</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">79.</div>
    <div class="ref-body">
    <span class="ref-author">Orth, C., N. Niemann, L. Hennig, L.-O. Essen, A. Batschauer</span>,
    <span class="ref-title">Hyperactivity of the Arabidopsis cryptochrome (cry1) L407F mutant is caused by a structural alteration close to the cry1 ATP-binding site</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">292</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">12906&ndash;12920</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m117.788869">http://doi.org/10.1074/jbc.m117.788869</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">80.</div>
    <div class="ref-body">
    <span class="ref-author">Pimviriyakul, P., K. Thotsaporn, J. Sucharitakul, P. Chaiyen</span>,
    <span class="ref-title">Kinetic Mechanism of the Dechlorinating Flavin-dependent Monooxygenase HadA</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">292</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">4818&ndash;4832</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m116.774448">http://doi.org/10.1074/jbc.m116.774448</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">81.</div>
    <div class="ref-body">
    <span class="ref-author">Plach, M.G., F. Semmelmann, F. Busch, M. Busch, L. Heizinger, V.H. Wysocki, R. Merkl, R. Sterner</span>,
    <span class="ref-title">Evolutionary diversification of protein&ndash;protein interactions by interface add-ons</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">114</span>(<span class="ref-number">40</span>):    p. <span class="ref-page">E8333&ndash;E8342</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1707335114">http://doi.org/10.1073/pnas.1707335114</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">82.</div>
    <div class="ref-body">
    <span class="ref-author">Pornsuwan, S., S. Maenpuen, P. Kamutira, P. Watthaisong, K. Thotsaporn, C. Tongsook, M. Juttulapa, S. Nijvipakul, P. Chaiyen</span>,
    <span class="ref-title">3,4-Dihydroxyphenylacetate 2,3-dioxygenase from Pseudomonas aeruginosa: An Fe(II)-containing enzyme with fast turnover</span>.
    <span class="ref-pub">PLOS ONE,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">e0171135</span>.    <span class="ref-url"><a href="http://doi.org/10.1371/journal.pone.0171135">http://doi.org/10.1371/journal.pone.0171135</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">83.</div>
    <div class="ref-body">
    <span class="ref-author">Repka, L.M., J.R. Chekan, S.K. Nair, W.A. van der Donk</span>,
    <span class="ref-title">Mechanistic Understanding of Lanthipeptide Biosynthetic Enzymes</span>.
    <span class="ref-pub">Chemical Reviews,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">117</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">5457&ndash;5520</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.chemrev.6b00591">http://doi.org/10.1021/acs.chemrev.6b00591</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">84.</div>
    <div class="ref-body">
    <span class="ref-author">Rudolf, J.D., C.-Y. Chang, M. Ma, B. Shen</span>,
    <span class="ref-title">Cytochromes P450 for natural product biosynthesis in Streptomyces: sequence, structure, and function</span>.
    <span class="ref-pub">Natural Product Reports,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">34</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1141&ndash;1172</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c7np00034k">http://doi.org/10.1039/c7np00034k</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">85.</div>
    <div class="ref-body">
    <span class="ref-author">Schwalen, C.J., X. Feng, W. Liu, B. O-Dowd, T.-P. Ko, C.J. Shin, R.-T. Guo, D.A. Mitchell, E. Oldfield</span>,
    <span class="ref-title">Head-to-Head Prenyl Synthases in Pathogenic Bacteria</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">18</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">985&ndash;991</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201700099">http://doi.org/10.1002/cbic.201700099</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">86.</div>
    <div class="ref-body">
    <span class="ref-author">Tietz, J.I., C.J. Schwalen, P.S. Patel, T. Maxson, P.M. Blair, H.-C. Tai, U.I. Zakai, D.A. Mitchell</span>,
    <span class="ref-title">A new genome-mining tool redefines the lasso peptide biosynthetic landscape</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">5</span>):    p. <span class="ref-page">470&ndash;478</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchembio.2319">http://doi.org/10.1038/nchembio.2319</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">87.</div>
    <div class="ref-body">
    <span class="ref-author">V&aacute;zquez, R., M. Domenech, M. Iglesias-Bexiga, M. Men&eacute;ndez, P. Garc&iacute;a</span>,
    <span class="ref-title">Csl2, a novel chimeric bacteriophage lysin to fight infections caused by Streptococcus suis, an emerging zoonotic pathogen</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">7</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41598-017-16736-0">http://doi.org/10.1038/s41598-017-16736-0</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">88.</div>
    <div class="ref-body">
    <span class="ref-author">Wagner, D.T., J. Zeng, C.B. Bailey, D.C. Gay, F. Yuan, H.R. Manion, A.T. Keatinge-Clay</span>,
    <span class="ref-title">Structural and Functional Trends in Dehydrating Bimodules from trans-Acyltransferase Polyketide Synthases</span>.
    <span class="ref-pub">Structure,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">25</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">1045&ndash;1055.e2</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.str.2017.05.011">http://doi.org/10.1016/j.str.2017.05.011</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">89.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, H., X. Chen, C. Li, Y. Liu, F. Yang, C. Wang</span>,
    <span class="ref-title">Sequence-Based Prediction of Cysteine Reactivity Using Machine Learning</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">451&ndash;460</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.7b00897">http://doi.org/10.1021/acs.biochem.7b00897</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">90.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, M., L. Moyni&eacute;, P.J. Harrison, V. Kelly, A. Piper, J.H. Naismith, D.J. Campopiano</span>,
    <span class="ref-title">Using the pimeloyl-CoA synthetase adenylation fold to synthesize fatty acid thioesters</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">660&ndash;667</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/nchembio.2361">http://doi.org/10.1038/nchembio.2361</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">91.</div>
    <div class="ref-body">
    <span class="ref-author">Welsh, M.A., A. Taguchi, K. Schaefer, D.V. Tyne, F. Lebreton, M.S. Gilmore, D. Kahne, S. Walker</span>,
    <span class="ref-title">Identification of a Functionally Unique Family of Penicillin-Binding Proteins</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">139</span>(<span class="ref-number">49</span>):    p. <span class="ref-page">17727&ndash;17730</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.7b10170">http://doi.org/10.1021/jacs.7b10170</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">92.</div>
    <div class="ref-body">
    <span class="ref-author">Yuan, H., J. Zhang, Y. Cai, S. Wu, K. Yang, H.C.S. Chan, W. Huang, W.-B. Jin, Y. Li, Y. Yin, Y. Igarashi, S. Yuan, J. Zhou, G.-L. Tang</span>,
    <span class="ref-title">GyrI-like proteins catalyze cyclopropanoid hydrolysis to confer cellular protection</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">8</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-017-01508-1">http://doi.org/10.1038/s41467-017-01508-1</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">93.</div>
    <div class="ref-body">
    <span class="ref-author">Zallot, R., Y. Yuan, V. de Cr&eacute;cy-Lagard</span>,
    <span class="ref-title">The Escherichia coli COG1738 Member YhhQ Is Involved in 7-Cyanodeazaguanine (preQ0) Transport</span>.
    <span class="ref-pub">Biomolecules,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">7</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">12</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/biom7010012">http://doi.org/10.3390/biom7010012</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">94.</div>
    <div class="ref-body">
    <span class="ref-author">Zhong, G., Q. Zhao, Q. Zhang, W. Liu</span>,
    <span class="ref-title">4-alkyl-L-(Dehydro)proline biosynthesis in actinobacteria involves N-terminal nucleophile-hydrolase activity of &gamma;-glutamyltranspeptidase homolog for C-C bond cleavage</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">8</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/ncomms16109">http://doi.org/10.1038/ncomms16109</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">95.</div>
    <div class="ref-body">
    <span class="ref-author">An, L., D.P. Cogan, C.D. Navo, G. Jim&eacute;nez-Os&eacute;s, S.K. Nair, W.A. van der Donk</span>,
    <span class="ref-title">Substrate-assisted enzymatic formation of lysinoalanine in duramycin</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">928&ndash;933</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41589-018-0122-4">http://doi.org/10.1038/s41589-018-0122-4</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">96.</div>
    <div class="ref-body">
    <span class="ref-author">Annaval, T., L. Han, J.D. Rudolf, G. Xie, D. Yang, C.-Y. Chang, M. Ma, I. Crnovcic, M.D. Miller, J. Soman, W. Xu, G.N. Phillips, B. Shen</span>,
    <span class="ref-title">Biochemical and Structural Characterization of TtnD, a Prenylated FMN-Dependent Decarboxylase from the Tautomycetin Biosynthetic Pathway</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">2728&ndash;2738</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.8b00673">http://doi.org/10.1021/acschembio.8b00673</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">97.</div>
    <div class="ref-body">
    <span class="ref-author">Ayikpoe, R., J. Salazar, B. Majestic, J.A. Latham</span>,
    <span class="ref-title">Mycofactocin Biosynthesis Proceeds through 3-Amino-5-[(p-hydroxyphenyl) methyl]-4,4-dimethyl-2-pyrrolidinone (AHDP); Direct Observation of MftE Specificity toward MftA</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">37</span>):    p. <span class="ref-page">5379&ndash;5383</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00816">http://doi.org/10.1021/acs.biochem.8b00816</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">98.</div>
    <div class="ref-body">
    <span class="ref-author">Bastard, K., T. Isabet, E.A. Stura, P. Legrand, A. Zaparucha</span>,
    <span class="ref-title">Structural Studies based on two Lysine Dioxygenases with Distinct Regioselectivity Brings Insights Into Enzyme Specificity within the Clavaminate Synthase-Like Family</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">8</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41598-018-34795-9">http://doi.org/10.1038/s41598-018-34795-9</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">99.</div>
    <div class="ref-body">
    <span class="ref-author">Blair, P.M., M.L. Land, M.J. Piatek, D.A. Jacobson, T.-Y.S. Lu, M.J. Doktycz, D.A. Pelletier</span>,
    <span class="ref-title">Exploration of the Biosynthetic Potential of the PopulusMicrobiome</span>.
    <span class="ref-pub">mSystems,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">3</span>(<span class="ref-number">5</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/msystems.00045-18">http://doi.org/10.1128/msystems.00045-18</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">100.</div>
    <div class="ref-body">
    <span class="ref-author">Bridwell-Rabb, J., T.A. Grell, C.L. Drennan</span>,
    <span class="ref-title">A Rich Man, Poor Man Story of <i>S</i>-Adenosylmethionine and Cobalamin Revisited</span>.
    <span class="ref-pub">Annual Review of Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">87</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">555&ndash;584</span>.    <span class="ref-url"><a href="http://doi.org/10.1146/annurev-biochem-062917-012500">http://doi.org/10.1146/annurev-biochem-062917-012500</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">101.</div>
    <div class="ref-body">
    <span class="ref-author">Bushin, L.B., K.A. Clark, I. Pelczer, M.R. Seyedsayamdost</span>,
    <span class="ref-title">Charting an Unexplored Streptococcal Biosynthetic Landscape Reveals a Unique Peptide Cyclization Motif</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">140</span>(<span class="ref-number">50</span>):    p. <span class="ref-page">17674&ndash;17684</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b10266">http://doi.org/10.1021/jacs.8b10266</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">102.</div>
    <div class="ref-body">
    <span class="ref-author">Calhoun, S., M. Korczynska, D.J. Wichelecki, B. San Francisco, S. Zhao, D.A. Rodionov, M.W. Vetting, N.F. Al-Obaidi, H. Lin, M.J. O'Meara, D.A. Scott, J.H. Morris, D. Russel, S.C. Almo, A.L. Osterman, J.A. Gerlt, M.P. Jacobson, B.K. Shoichet, A. Sali</span>,
    <span class="ref-title">Prediction of enzymatic pathways by integrative pathway mapping</span>.
    <span class="ref-pub">eLife,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">7</span>:.    <span class="ref-url"><a href="http://doi.org/10.7554/elife.31097">http://doi.org/10.7554/elife.31097</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">103.</div>
    <div class="ref-body">
    <span class="ref-author">Chakravarti, A., K. Selvadurai, R. Shahoei, H. Lee, S. Fatma, E. Tajkhorshid, R.H. Huang</span>,
    <span class="ref-title">Reconstitution and substrate specificity for isopentenyl pyrophosphate of the antiviral radical SAM enzyme viperin</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">293</span>(<span class="ref-number">36</span>):    p. <span class="ref-page">14122&ndash;14133</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra118.003998">http://doi.org/10.1074/jbc.ra118.003998</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">104.</div>
    <div class="ref-body">
    <span class="ref-author">Chang, C.-Y., X. Yan, I. Crnovcic, T. Annaval, C. Chang, B. Nocek, J.D. Rudolf, D. Yang, . Hindra, G. Babnigg, A. Joachimiak, G.N. Phillips, B. Shen</span>,
    <span class="ref-title">Resistance to Enediyne Antitumor Antibiotics by Sequestration</span>.
    <span class="ref-pub">Cell Chemical Biology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">25</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1075&ndash;1085.e4</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.chembiol.2018.05.012">http://doi.org/10.1016/j.chembiol.2018.05.012</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">105.</div>
    <div class="ref-body">
    <span class="ref-author">Copp, J.N., E. Akiva, P.C. Babbitt, N. Tokuriki</span>,
    <span class="ref-title">Revealing Unexplored Sequence-Function Space Using Sequence Similarity Networks</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">4651&ndash;4662</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00473">http://doi.org/10.1021/acs.biochem.8b00473</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">106.</div>
    <div class="ref-body">
    <span class="ref-author">Dalponte, L., A. Parajuli, E. Younger, A. Mattila, J. Jokela, M. Wahlsten, N. Leikoski, K. Sivonen, S.A. Jarmusch, W.E. Houssen, D.P. Fewer</span>,
    <span class="ref-title">N-Prenylation of Tryptophan by an Aromatic Prenyltransferase from the Cyanobactin Biosynthetic Pathway</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">50</span>):    p. <span class="ref-page">6860&ndash;6867</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00879">http://doi.org/10.1021/acs.biochem.8b00879</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">107.</div>
    <div class="ref-body">
    <span class="ref-author">Dong, L.-B., J.D. Rudolf, M.-R. Deng, X. Yan, B. Shen</span>,
    <span class="ref-title">Discovery of the Tiancilactone Antibiotics by Genome Mining of Atypical Bacterial Type&nbsp;II Diterpene Synthases</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">19</span>(<span class="ref-number">16</span>):    p. <span class="ref-page">1727&ndash;1733</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201800285">http://doi.org/10.1002/cbic.201800285</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">108.</div>
    <div class="ref-body">
    <span class="ref-author">Dunkle, J.A., M.R. Bruno, F.W. Outten, P.A. Frantom</span>,
    <span class="ref-title">Structural Evidence for Dimer-Interface-Driven Regulation of the Type II Cysteine Desulfurase, SufS</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">687&ndash;696</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b01122">http://doi.org/10.1021/acs.biochem.8b01122</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">109.</div>
    <div class="ref-body">
    <span class="ref-author">Fisher, O.S., G.E. Kenney, M.O. Ross, S.Y. Ro, B.E. Lemma, S. Batelu, P.M. Thomas, V.C. Sosnowski, C.J. DeHart, N.L. Kelleher, T.L. Stemmler, B.M. Hoffman, A.C. Rosenzweig</span>,
    <span class="ref-title">Characterization of a long overlooked copper protein from methane- and ammonia-oxidizing bacteria</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-018-06681-5">http://doi.org/10.1038/s41467-018-06681-5</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">110.</div>
    <div class="ref-body">
    <span class="ref-author">Gilchrist, C.L.M., H. Li, Y.-H. Chooi</span>,
    <span class="ref-title">Panning for gold in mould: can we increase the odds for fungal genome mining?</span>.
    <span class="ref-pub">Organic & Biomolecular Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">16</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">1620&ndash;1626</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c7ob03127k">http://doi.org/10.1039/c7ob03127k</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">111.</div>
    <div class="ref-body">
    <span class="ref-author">Green, C.M., O. Novikova, M. Belfort</span>,
    <span class="ref-title">The dynamic intein landscape of eukaryotes</span>.
    <span class="ref-pub">Mobile DNA,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s13100-018-0111-x">http://doi.org/10.1186/s13100-018-0111-x</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">112.</div>
    <div class="ref-body">
    <span class="ref-author">Han, L., J. Yuan, X. Ao, S. Lin, X. Han, H. Ye</span>,
    <span class="ref-title">Biochemical Characterization and Phylogenetic Analysis of the Virulence Factor Lysine Decarboxylase From Vibrio vulnificus</span>.
    <span class="ref-pub">Frontiers in Microbiology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fmicb.2018.03082">http://doi.org/10.3389/fmicb.2018.03082</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">113.</div>
    <div class="ref-body">
    <span class="ref-author">Ho, C.L., H.Q. Tan, K.J. Chua, A. Kang, K.H. Lim, K.L. Ling, W.S. Yew, Y.S. Lee, J.P. Thiery, M.W. Chang</span>,
    <span class="ref-title">Engineered commensal microbes for diet-mediated colorectal-cancer chemoprevention</span>.
    <span class="ref-pub">Nature Biomedical Engineering,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">2</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">27&ndash;37</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41551-017-0181-y">http://doi.org/10.1038/s41551-017-0181-y</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">114.</div>
    <div class="ref-body">
    <span class="ref-author">Hogancamp, T.N., M.F. Mabanglo, F.M. Raushel</span>,
    <span class="ref-title">Structure and Reaction Mechanism of the LigJ Hydratase: An Enzyme Critical for the Bacterial Degradation of Lignin in the Protocatechuate 4,5-Cleavage Pathway</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">40</span>):    p. <span class="ref-page">5841&ndash;5850</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00713">http://doi.org/10.1021/acs.biochem.8b00713</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">115.</div>
    <div class="ref-body">
    <span class="ref-author">Hossain, G.S., S.P. Nadarajan, L. Zhang, T.-K. Ng, J.L. Foo, H. Ling, W.J. Choi, M.W. Chang</span>,
    <span class="ref-title">Rewriting the Metabolic Blueprint: Advances in Pathway Diversification in Microorganisms</span>.
    <span class="ref-pub">Frontiers in Microbiology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fmicb.2018.00155">http://doi.org/10.3389/fmicb.2018.00155</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">116.</div>
    <div class="ref-body">
    <span class="ref-author">Jeoung, J.-H., H. Dobbek</span>,
    <span class="ref-title">ATP-dependent substrate reduction at an [Fe8S9] double-cubane cluster</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">115</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">2994&ndash;2999</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1720489115">http://doi.org/10.1073/pnas.1720489115</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">117.</div>
    <div class="ref-body">
    <span class="ref-author">Ji, X., D. Mandalapu, J. Cheng, W. Ding, Q. Zhang</span>,
    <span class="ref-title">Expanding the Chemistry of the Class C Radical SAM Methyltransferase NosN by Using an Allyl Analogue of SAM</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">22</span>):    p. <span class="ref-page">6601&ndash;6604</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201712224">http://doi.org/10.1002/anie.201712224</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">118.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., Z.J. Pu, K. Tang, X. Jia, K.H. Kim, X. Liu, C.O. Jeon</span>,
    <span class="ref-title">Catalytic, Computational, and Evolutionary Analysis of the <small>D</small>-Lactate Dehydrogenases Responsible for <small>D</small>-Lactic Acid Production in Lactic Acid Bacteria</span>.
    <span class="ref-pub">Journal of Agricultural and Food Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">66</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">8371&ndash;8381</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.jafc.8b02454">http://doi.org/10.1021/acs.jafc.8b02454</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">119.</div>
    <div class="ref-body">
    <span class="ref-author">Jiang, S., T. Lin, Q. Xie, L. Wang</span>,
    <span class="ref-title">Network Analysis of RAD51 Proteins in Metazoa and the Evolutionary Relationships With Their Archaeal Homologs</span>.
    <span class="ref-pub">Frontiers in Genetics,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fgene.2018.00383">http://doi.org/10.3389/fgene.2018.00383</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">120.</div>
    <div class="ref-body">
    <span class="ref-author">Kenney, G.E., L.M.K. Dassama, M.-E. Pandelia, A.S. Gizzi, R.J. Martinie, P. Gao, C.J. DeHart, L.F. Schachner, O.S. Skinner, S.Y. Ro, X. Zhu, M. Sadek, P.M. Thomas, S.C. Almo, J.M. Bollinger, C. Krebs, N.L. Kelleher, A.C. Rosenzweig</span>,
    <span class="ref-title">The biosynthesis of methanobactin</span>.
    <span class="ref-pub">Science,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">359</span>(<span class="ref-number">6382</span>):    p. <span class="ref-page">1411&ndash;1416</span>.    <span class="ref-url"><a href="http://doi.org/10.1126/science.aap9437">http://doi.org/10.1126/science.aap9437</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">121.</div>
    <div class="ref-body">
    <span class="ref-author">Kim, K.H., X. Jia, B. Jia, C.O. Jeon</span>,
    <span class="ref-title">Identification and Characterization of <small>L</small>-Malate Dehydrogenases and the <small>L</small>-Lactate-Biosynthetic Pathway in Leuconostoc mesenteroides ATCC 8293</span>.
    <span class="ref-pub">Journal of Agricultural and Food Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">66</span>(<span class="ref-number">30</span>):    p. <span class="ref-page">8086&ndash;8093</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.jafc.8b02649">http://doi.org/10.1021/acs.jafc.8b02649</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">122.</div>
    <div class="ref-body">
    <span class="ref-author">Koppel, N., J.E. Bisanz, M.-E. Pandelia, P.J. Turnbaugh, E.P. Balskus</span>,
    <span class="ref-title">Discovery and characterization of a prevalent human gut bacterial enzyme sufficient for the inactivation of a family of plant toxins</span>.
    <span class="ref-pub">eLife,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">7</span>:.    <span class="ref-url"><a href="http://doi.org/10.7554/elife.33953">http://doi.org/10.7554/elife.33953</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">123.</div>
    <div class="ref-body">
    <span class="ref-author">Lei, L., K.P. Cherukuri, U. Alcolombri, D. Meltzer, D.S. Tawfik</span>,
    <span class="ref-title">The Dimethylsulfoniopropionate (DMSP) Lyase and Lyase-Like Cupin Family Consists of Bona Fide DMSP lyases as Well as Other Enzymes with Unknown Function</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">24</span>):    p. <span class="ref-page">3364&ndash;3377</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00097">http://doi.org/10.1021/acs.biochem.8b00097</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">124.</div>
    <div class="ref-body">
    <span class="ref-author">Leong, R., D. Urano</span>,
    <span class="ref-title">Molecular Breeding for Plant Factory: Strategies and Technology</span>.
    <span class="ref-pub">Smart Plant Factory,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-url"><a href="http://doi.org/10.1007/978-981-13-1065-2_19">http://doi.org/10.1007/978-981-13-1065-2_19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">125.</div>
    <div class="ref-body">
    <span class="ref-author">Li, Y.-X., Z. Zhong, W.-P. Zhang, P.-Y. Qian</span>,
    <span class="ref-title">Discovery of cationic nonribosomal peptides as Gram-negative antibiotics through global genome mining</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-018-05781-6">http://doi.org/10.1038/s41467-018-05781-6</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">126.</div>
    <div class="ref-body">
    <span class="ref-author">Liao, L., A.L. Schaefer, B.G. Coutinho, P.J.B. Brown, E.P. Greenberg</span>,
    <span class="ref-title">An aryl-homoserine lactone quorum-sensing signal produced by a dimorphic prosthecate bacterium</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">115</span>(<span class="ref-number">29</span>):    p. <span class="ref-page">7587&ndash;7592</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1808351115">http://doi.org/10.1073/pnas.1808351115</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">127.</div>
    <div class="ref-body">
    <span class="ref-author">Liu, D., Y. Wei, X. Liu, Y. Zhou, L. Jiang, J. Yin, F. Wang, Y. Hu, A.N.N. Urs, Y. Liu, E.L. Ang, S. Zhao, H. Zhao, Y. Zhang</span>,
    <span class="ref-title">Indoleacetate decarboxylase is a glycyl radical enzyme catalysing the formation of malodorant skatole</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-018-06627-x">http://doi.org/10.1038/s41467-018-06627-x</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">128.</div>
    <div class="ref-body">
    <span class="ref-author">Luo, S., H. Huang</span>,
    <span class="ref-title">Discovering a new catabolic pathway of D-ribonate in Mycobacterium smegmatis</span>.
    <span class="ref-pub">Biochemical and Biophysical Research Communications,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">505</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">1107&ndash;1111</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbrc.2018.10.033">http://doi.org/10.1016/j.bbrc.2018.10.033</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">129.</div>
    <div class="ref-body">
    <span class="ref-author">Mahanta, N., A. Liu, S. Dong, S.K. Nair, D.A. Mitchell</span>,
    <span class="ref-title">Enzymatic reconstitution of ribosomal peptide backbone thioamidation</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">115</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">3030&ndash;3035</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1722324115">http://doi.org/10.1073/pnas.1722324115</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">130.</div>
    <div class="ref-body">
    <span class="ref-author">Mallette, E., M.S. Kimber</span>,
    <span class="ref-title">Structure and Kinetics of the <i>S</i>-(+)-1-Amino-2-propanol Dehydrogenase from the RMM Microcompartment of Mycobacterium smegmatis</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">26</span>):    p. <span class="ref-page">3780&ndash;3789</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00464">http://doi.org/10.1021/acs.biochem.8b00464</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">131.</div>
    <div class="ref-body">
    <span class="ref-author">Mallette, E., M.S. Kimber</span>,
    <span class="ref-title">Structural and kinetic characterization of (S)-1-amino-2-propanol kinase from the aminoacetone utilization microcompartment of Mycobacterium smegmatis</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">293</span>(<span class="ref-number">51</span>):    p. <span class="ref-page">19909&ndash;19918</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra118.005485">http://doi.org/10.1074/jbc.ra118.005485</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">132.</div>
    <div class="ref-body">
    <span class="ref-author">Mehrer, C.R., M.R. Incha, M.C. Politz, B.F. Pfleger</span>,
    <span class="ref-title">Anaerobic production of medium-chain fatty alcohols via a &beta;-reduction pathway</span>.
    <span class="ref-pub">Metabolic Engineering,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">48</span>:    p. <span class="ref-page">63&ndash;71</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.ymben.2018.05.011">http://doi.org/10.1016/j.ymben.2018.05.011</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">133.</div>
    <div class="ref-body">
    <span class="ref-author">Mukherjee, K., T. Narindoshvili, F.M. Raushel</span>,
    <span class="ref-title">Discovery of a Kojibiose Phosphorylase in Escherichia coli K-12</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">19</span>):    p. <span class="ref-page">2857&ndash;2867</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00392">http://doi.org/10.1021/acs.biochem.8b00392</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">134.</div>
    <div class="ref-body">
    <span class="ref-author">Nemmara, V.V., D.F. Xiang, A.A. Fedorov, E.V. Fedorov, J.B. Bonanno, S.C. Almo, F.M. Raushel</span>,
    <span class="ref-title">Substrate Profile of the Phosphotriesterase Homology Protein from Escherichia coli</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">43</span>):    p. <span class="ref-page">6219&ndash;6227</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00935">http://doi.org/10.1021/acs.biochem.8b00935</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">135.</div>
    <div class="ref-body">
    <span class="ref-author">Ongpipattanakul, C., S.K. Nair</span>,
    <span class="ref-title">Molecular Basis for Autocatalytic Backbone N-Methylation in RiPP Natural Product Biosynthesis</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">2989&ndash;2999</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.8b00668">http://doi.org/10.1021/acschembio.8b00668</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">136.</div>
    <div class="ref-body">
    <span class="ref-author">Punekar, N.S.</span>,
    <span class="ref-title">Future of Enzymology: An Appraisal</span>.
    <span class="ref-pub">ENZYMES: Catalysis, Kinetics and Mechanisms,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-url"><a href="http://doi.org/10.1007/978-981-13-0785-0_39">http://doi.org/10.1007/978-981-13-0785-0_39</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">137.</div>
    <div class="ref-body">
    <span class="ref-author">Purohit, R., M.O. Ross, S. Batelu, A. Kusowski, T.L. Stemmler, B.M. Hoffman, A.C. Rosenzweig</span>,
    <span class="ref-title">Cu+-specific CopB transporter: Revising P1B-type ATPase classification</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">115</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">2108&ndash;2113</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1721783115">http://doi.org/10.1073/pnas.1721783115</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">138.</div>
    <div class="ref-body">
    <span class="ref-author">Qiu, B., B. Xia, Q. Zhou, Y. Lu, M. He, K. Hasegawa, Z. Ma, F. Zhang, L. Gu, Q. Mao, F. Wang, S. Zhao, Z. Gao, J. Liao</span>,
    <span class="ref-title">Succinate-acetate permease from Citrobacter koseri is an anion channel that unidirectionally translocates acetate</span>.
    <span class="ref-pub">Cell Research,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">28</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">644&ndash;654</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41422-018-0032-8">http://doi.org/10.1038/s41422-018-0032-8</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">139.</div>
    <div class="ref-body">
    <span class="ref-author">Reichelt, R., D. Grohmann, S. Willkomm</span>,
    <span class="ref-title">A journey through the evolutionary diversification of archaeal Lsm and Hfq proteins</span>.
    <span class="ref-pub">Emerging Topics in Life Sciences,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">2</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">647&ndash;657</span>.    <span class="ref-url"><a href="http://doi.org/10.1042/etls20180034">http://doi.org/10.1042/etls20180034</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">140.</div>
    <div class="ref-body">
    <span class="ref-author">Rose, H.R., M.K. Ghosh, A.O. Maggiolo, C.J. Pollock, E.J. Blaesi, V. Hajj, Y. Wei, L.J. Rajakovich, W. Chang, Y. Han, M. Hajj, C. Krebs, A. Silakov, M.-E. Pandelia, J.M. Bollinger, A.K. Boal</span>,
    <span class="ref-title">Structural Basis for Superoxide Activation of Flavobacterium johnsoniae Class I Ribonucleotide Reductase and for Radical Initiation by Its Dimanganese Cofactor</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">18</span>):    p. <span class="ref-page">2679&ndash;2693</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00247">http://doi.org/10.1021/acs.biochem.8b00247</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">141.</div>
    <div class="ref-body">
    <span class="ref-author">Roy, R., S. Samanta, S. Patra, N.K. Mahato, R.P. Saha</span>,
    <span class="ref-title">In silico identification and characterization of sensory motifs in the transcriptional regulators of the ArsR-SmtB family</span>.
    <span class="ref-pub">Metallomics,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">1476&ndash;1500</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c8mt00082d">http://doi.org/10.1039/c8mt00082d</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">142.</div>
    <div class="ref-body">
    <span class="ref-author">Ryu, H., T.L. Grove, S.C. Almo, J. Kim</span>,
    <span class="ref-title">Identification of a novel tRNA wobble uridine modifying activity in the biosynthesis of 5-methoxyuridine</span>.
    <span class="ref-pub">Nucleic Acids Research,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">46</span>(<span class="ref-number">17</span>):    p. <span class="ref-page">9160&ndash;9169</span>.    <span class="ref-url"><a href="http://doi.org/10.1093/nar/gky592">http://doi.org/10.1093/nar/gky592</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">143.</div>
    <div class="ref-body">
    <span class="ref-author">Stojkovi&cacute;, V., T. Chu, G. Therizols, D.E. Weinberg, D.G. Fujimori</span>,
    <span class="ref-title">miCLIP-MaPseq, a Substrate Identification Approach for Radical SAM RNA Methylating Enzymes</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">140</span>(<span class="ref-number">23</span>):    p. <span class="ref-page">7135&ndash;7143</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b02618">http://doi.org/10.1021/jacs.8b02618</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">144.</div>
    <div class="ref-body">
    <span class="ref-author">Taylor, Z.W., F.M. Raushel</span>,
    <span class="ref-title">Cytidine Diphosphoramidate Kinase: An Enzyme Required for the Biosynthesis of the O-Methyl Phosphoramidate Modification in the Capsular Polysaccharides of Campylobacter jejuni</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">15</span>):    p. <span class="ref-page">2238&ndash;2244</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00279">http://doi.org/10.1021/acs.biochem.8b00279</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">145.</div>
    <div class="ref-body">
    <span class="ref-author">Viana, A.T., T. Caetano, C. Covas, T. Santos, S. Mendo</span>,
    <span class="ref-title">Environmental superbugs: The case study of Pedobacter spp.</span>.
    <span class="ref-pub">Environmental Pollution,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">241</span>:    p. <span class="ref-page">1048&ndash;1055</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.envpol.2018.06.047">http://doi.org/10.1016/j.envpol.2018.06.047</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">146.</div>
    <div class="ref-body">
    <span class="ref-author">Vogt, M.S., S.L. Völpel, S.-V. Albers, L.-O. Essen, A. Banerjee</span>,
    <span class="ref-title">Crystal structure of an Lrs14-like archaeal biofilm regulator from Sulfolobus acidocaldarius</span>.
    <span class="ref-pub">Acta Crystallographica Section D Structural Biology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">74</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">1105&ndash;1114</span>.    <span class="ref-url"><a href="http://doi.org/10.1107/s2059798318014146">http://doi.org/10.1107/s2059798318014146</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">147.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, S.C.</span>,
    <span class="ref-title">Cobalamin-dependent radicalS-adenosyl-<small>L</small>-methionine enzymes in natural product biosynthesis</span>.
    <span class="ref-pub">Natural Product Reports,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">35</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">707&ndash;720</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c7np00059f">http://doi.org/10.1039/c7np00059f</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">148.</div>
    <div class="ref-body">
    <span class="ref-author">Ward, A., N. Allenby</span>,
    <span class="ref-title">Genome mining for the search and discovery of bioactive compounds: The Streptomyces paradigm</span>.
    <span class="ref-pub">FEMS Microbiology Letters,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-url"><a href="http://doi.org/10.1093/femsle/fny240">http://doi.org/10.1093/femsle/fny240</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">149.</div>
    <div class="ref-body">
    <span class="ref-author">Wood, B.M., J.P.S. Maria, L.M. Matano, C.R. Vickery, S. Walker</span>,
    <span class="ref-title">A partial reconstitution implicates DltD in catalyzing lipoteichoic acid <small>D</small>-alanylation</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">293</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">17985&ndash;17996</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra118.004561">http://doi.org/10.1074/jbc.ra118.004561</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">150.</div>
    <div class="ref-body">
    <span class="ref-author">Yan, X.-F., L. Xin, J.T. Yen, Y. Zeng, S. Jin, Q.W. Cheang, R.A.C.Y. Fong, K.-H. Chiam, Z.-X. Liang, Y.-G. Gao</span>,
    <span class="ref-title">Structural analyses unravel the molecular mechanism of cyclic di-GMP regulation of bacterial chemotaxis via a PilZ adaptor protein</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2017</span>.
    <span class="ref-volume">293</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">100&ndash;111</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.m117.815704">http://doi.org/10.1074/jbc.m117.815704</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">151.</div>
    <div class="ref-body">
    <span class="ref-author">Yuenyao, A., N. Petchyam, N. Kamonsutthipaijit, P. Chaiyen, D. Pakotiprapha</span>,
    <span class="ref-title">Crystal structure of the flavin reductase of Acinetobacter baumannii p-hydroxyphenylacetate 3-hydroxylase (HPAH) and identification of amino acid residues underlying its regulation by aromatic ligands</span>.
    <span class="ref-pub">Archives of Biochemistry and Biophysics,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">653</span>:    p. <span class="ref-page">24&ndash;38</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.abb.2018.06.010">http://doi.org/10.1016/j.abb.2018.06.010</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">152.</div>
    <div class="ref-body">
    <span class="ref-author">Reis, R.A.G., F. Salvi, I. Williams, G. Gadda</span>,
    <span class="ref-title">Kinetic Investigation of a Presumed Nitronate Monooxygenase from Pseudomonas aeruginosa PAO1 Establishes a New Class of NAD(P)H: Quinone Reductases</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">22</span>):    p. <span class="ref-page">2594&ndash;2607</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00207">http://doi.org/10.1021/acs.biochem.9b00207</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">153.</div>
    <div class="ref-body">
    <span class="ref-author">Amatuni, A., H. Renata</span>,
    <span class="ref-title">Identification of a lysine 4-hydroxylase from the glidobactin biosynthesis and evaluation of its biocatalytic potential</span>.
    <span class="ref-pub">Organic & Biomolecular Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">1736&ndash;1739</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c8ob02054j">http://doi.org/10.1039/c8ob02054j</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">154.</div>
    <div class="ref-body">
    <span class="ref-author">Bashiri, G., J. Antoney, E.N.M. Jirgis, M.V. Shah, B. Ney, J. Copp, S.M. Stuteley, S. Sreebhavan, B. Palmer, M. Middleditch, N. Tokuriki, C. Greening, C. Scott, E.N. Baker, C.J. Jackson</span>,
    <span class="ref-title">A revised biosynthetic pathway for the cofactor F420 in prokaryotes</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-019-09534-x">http://doi.org/10.1038/s41467-019-09534-x</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">155.</div>
    <div class="ref-body">
    <span class="ref-author">Bell, A., J. Brunt, E. Crost, L. Vaux, R. Nepravishta, C.D. Owen, D. Latousakis, A. Xiao, W. Li, X. Chen, M.A. Walsh, J. Claesen, J. Angulo, G.H. Thomas, N. Juge</span>,
    <span class="ref-title">Elucidation of a sialic acid metabolism pathway in mucus-foraging Ruminococcus gnavus unravels mechanisms of bacterial adaptation to the gut</span>.
    <span class="ref-pub">Nature Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">4</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">2393&ndash;2404</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41564-019-0590-7">http://doi.org/10.1038/s41564-019-0590-7</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">156.</div>
    <div class="ref-body">
    <span class="ref-author">Biernat, K.A., S.J. Pellock, A.P. Bhatt, M.M. Bivins, W.G. Walton, B.N.T. Tran, L. Wei, M.C. Snider, A.P. Cesmat, A. Tripathy, D.A. Erie, M.R. Redinbo</span>,
    <span class="ref-title">Structure, function, and inhibition of drug reactivating human gut microbial &beta;-glucuronidases</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41598-018-36069-w">http://doi.org/10.1038/s41598-018-36069-w</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">157.</div>
    <div class="ref-body">
    <span class="ref-author">Blaby-Haas, C.E., S.S. Merchant</span>,
    <span class="ref-title">Comparative and Functional Algal Genomics</span>.
    <span class="ref-pub">Annual Review of Plant Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">70</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">605&ndash;638</span>.    <span class="ref-url"><a href="http://doi.org/10.1146/annurev-arplant-050718-095841">http://doi.org/10.1146/annurev-arplant-050718-095841</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">158.</div>
    <div class="ref-body">
    <span class="ref-author">Bobeica, S.C., S.-H. Dong, L. Huo, N. Mazo, M.I. McLaughlin, G. Jim&eacute;nez-Os&eacute;s, S.K. Nair, W.A. van der Donk</span>,
    <span class="ref-title">Insights into AMS/PCAT transporters from biochemical and structural characterization of a double Glycine motif protease</span>.
    <span class="ref-pub">eLife,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">8</span>:.    <span class="ref-url"><a href="http://doi.org/10.7554/elife.42305">http://doi.org/10.7554/elife.42305</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">159.</div>
    <div class="ref-body">
    <span class="ref-author">Caruso, A., L.B. Bushin, K.A. Clark, R.J. Martinie, M.R. Seyedsayamdost</span>,
    <span class="ref-title">Radical Approach to Enzymatic &beta;-Thioether Bond Formation</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">990&ndash;997</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b11060">http://doi.org/10.1021/jacs.8b11060</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">160.</div>
    <div class="ref-body">
    <span class="ref-author">Chekan, J.R., G.Y. Lee, A.E. Gamal, T.N. Purdy, K.N. Houk, B.S. Moore</span>,
    <span class="ref-title">Bacterial Tetrabromopyrrole Debrominase Shares a Reductive Dehalogenation Strategy with Human Thyroid Deiodinase</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">52</span>):    p. <span class="ref-page">5329&ndash;5338</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00318">http://doi.org/10.1021/acs.biochem.9b00318</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">161.</div>
    <div class="ref-body">
    <span class="ref-author">Chekan, J.R., C. Ongpipattanakul, T.R. Wright, B. Zhang, J.M. Bollinger, L.J. Rajakovich, C. Krebs, R.M. Cicchillo, S.K. Nair</span>,
    <span class="ref-title">Molecular basis for enantioselective herbicide degradation imparted by aryloxyalkanoate dioxygenases in transgenic plants</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">116</span>(<span class="ref-number">27</span>):    p. <span class="ref-page">13299&ndash;13304</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1900711116">http://doi.org/10.1073/pnas.1900711116</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">162.</div>
    <div class="ref-body">
    <span class="ref-author">Chen, D., Q. Zhao, W. Liu</span>,
    <span class="ref-title">Discovery of caerulomycin/collismycin-type 2,2&prime;-bipyridine natural products in the genomic era</span>.
    <span class="ref-pub">Journal of Industrial Microbiology & Biotechnology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">46</span>(<span class="ref-number">3-4</span>):    p. <span class="ref-page">459&ndash;468</span>.    <span class="ref-url"><a href="http://doi.org/10.1007/s10295-018-2092-7">http://doi.org/10.1007/s10295-018-2092-7</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">163.</div>
    <div class="ref-body">
    <span class="ref-author">Chen, W., P.A. Frantom</span>,
    <span class="ref-title">Distinct mechanisms of substrate selectivity in the DRE-TIM metallolyase superfamily: A role for the LeuA dimer regulatory domain</span>.
    <span class="ref-pub">Archives of Biochemistry and Biophysics,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">664</span>:    p. <span class="ref-page">1&ndash;8</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.abb.2019.01.021">http://doi.org/10.1016/j.abb.2019.01.021</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">164.</div>
    <div class="ref-body">
    <span class="ref-author">Clark, J., A. Terwilliger, C. Nguyen, S. Green, C. Nobles, A. Maresso</span>,
    <span class="ref-title">Heme catabolism in the causative agent of anthrax</span>.
    <span class="ref-pub">Molecular Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">112</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">515&ndash;531</span>.    <span class="ref-url"><a href="http://doi.org/10.1111/mmi.14270">http://doi.org/10.1111/mmi.14270</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">165.</div>
    <div class="ref-body">
    <span class="ref-author">Clark, K.A., L.B. Bushin, M.R. Seyedsayamdost</span>,
    <span class="ref-title">Aliphatic Ether Bond Formation Expands the Scope of Radical SAM Enzymes in Natural Product Biosynthesis</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">27</span>):    p. <span class="ref-page">10610&ndash;10615</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b05151">http://doi.org/10.1021/jacs.9b05151</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">166.</div>
    <div class="ref-body">
    <span class="ref-author">Copp, J.N., D.W. Anderson, E. Akiva, P.C. Babbitt, N. Tokuriki</span>,
    <span class="ref-title">Exploring the sequence, function, and evolutionary space of protein superfamilies using sequence similarity networks and phylogenetic reconstructions</span>.
    <span class="ref-pub">Methods in Enzymology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-url"><a href="http://doi.org/10.1016/bs.mie.2019.03.015">http://doi.org/10.1016/bs.mie.2019.03.015</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">167.</div>
    <div class="ref-body">
    <span class="ref-author">Coscol&iacute;n, C., N. Katzke, A. Garc&iacute;a-Moyano, J. Navarro-Fern&aacute;ndez, D. Almendral, M. Mart&iacute;nez-Mart&iacute;nez, A. Bollinger, R. Bargiela, C. Gertler, T.N. Chernikova, D. Rojo, C. Barbas, H. Tran, O.V. Golyshina, R. Koch, M.M. Yakimov, G.E.K. Bjerga, P.N. Golyshin, K.-E. Jaeger, M. Ferrer</span>,
    <span class="ref-title">Bioprospecting Reveals Class III &Omega;-Transaminases Converting Bulky Ketones and Environmentally Relevant Polyamines</span>.
    <span class="ref-pub">Applied and Environmental Microbiology,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">85</span>(<span class="ref-number">2</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/aem.02404-18">http://doi.org/10.1128/aem.02404-18</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">168.</div>
    <div class="ref-body">
    <span class="ref-author">Danczak, R.E., M.D. Johnston, C. Kenah, M. Slattery, M.J. Wilkins</span>,
    <span class="ref-title">Capability for arsenic mobilization in groundwater is distributed across broad phylogenetic lineages</span>.
    <span class="ref-pub">PLOS ONE,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">e0221694</span>.    <span class="ref-url"><a href="http://doi.org/10.1371/journal.pone.0221694">http://doi.org/10.1371/journal.pone.0221694</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">169.</div>
    <div class="ref-body">
    <span class="ref-author">Desmarais, J.J., A.I. Flamholz, C. Blikstad, E.J. Dugan, T.G. Laughlin, L.M. Oltrogge, A.W. Chen, K. Wetmore, S. Diamond, J.Y. Wang, D.F. Savage</span>,
    <span class="ref-title">DABs are inorganic carbon pumps found throughout prokaryotic phyla</span>.
    <span class="ref-pub">Nature Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">4</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">2204&ndash;2215</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41564-019-0520-8">http://doi.org/10.1038/s41564-019-0520-8</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">170.</div>
    <div class="ref-body">
    <span class="ref-author">DiCaprio, A.J., A. Firouzbakht, G.A. Hudson, D.A. Mitchell</span>,
    <span class="ref-title">Enzymatic Reconstitution and Biosynthetic Investigation of the Lasso Peptide Fusilassin</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">290&ndash;297</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b09928">http://doi.org/10.1021/jacs.8b09928</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">171.</div>
    <div class="ref-body">
    <span class="ref-author">Dong, L.-B., Y.-C. Liu, A.J. Cepeda, E. Kalkreuter, M.-R. Deng, J.D. Rudolf, C. Chang, A. Joachimiak, G.N. Phillips, B. Shen</span>,
    <span class="ref-title">Characterization and Crystal Structure of a Nonheme Diiron Monooxygenase Involved in Platensimycin and Platencin Biosynthesis</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">12406&ndash;12412</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b06183">http://doi.org/10.1021/jacs.9b06183</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">172.</div>
    <div class="ref-body">
    <span class="ref-author">Dong, S.-H., A. Liu, N. Mahanta, D.A. Mitchell, S.K. Nair</span>,
    <span class="ref-title">Mechanistic Basis for Ribosomal Peptide Backbone Modifications</span>.
    <span class="ref-pub">ACS Central Science,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-url"><a href="http://doi.org/10.1021/acscentsci.9b00124">http://doi.org/10.1021/acscentsci.9b00124</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">173.</div>
    <div class="ref-body">
    <span class="ref-author">Dunbar, K.L., M. Dell, E.M. Molloy, F. Kloss, C. Hertweck</span>,
    <span class="ref-title">Reconstitution of Iterative Thioamidation in Closthioamide Biosynthesis Reveals Tailoring Strategy for Nonribosomal Peptide Backbones</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">37</span>):    p. <span class="ref-page">13014&ndash;13018</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201905992">http://doi.org/10.1002/anie.201905992</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">174.</div>
    <div class="ref-body">
    <span class="ref-author">Erb, T.J.</span>,
    <span class="ref-title">Back to the future: Why we need enzymology to build a synthetic metabolism of the future</span>.
    <span class="ref-pub">Beilstein Journal of Organic Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">15</span>:    p. <span class="ref-page">551&ndash;557</span>.    <span class="ref-url"><a href="http://doi.org/10.3762/bjoc.15.49">http://doi.org/10.3762/bjoc.15.49</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">175.</div>
    <div class="ref-body">
    <span class="ref-author">Ervin, S.M., R.P. Hanley, L. Lim, W.G. Walton, K.H. Pearce, A.P. Bhatt, L.I. James, M.R. Redinbo</span>,
    <span class="ref-title">Targeting Regorafenib-Induced Toxicity through Inhibition of Gut Microbial &beta;-Glucuronidases</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">2737&ndash;2744</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.9b00663">http://doi.org/10.1021/acschembio.9b00663</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">176.</div>
    <div class="ref-body">
    <span class="ref-author">Ervin, S.M., H. Li, L. Lim, L.R. Roberts, X. Liang, S. Mani, M.R. Redinbo</span>,
    <span class="ref-title">Gut microbial &beta;-glucuronidases reactivate estrogens as components of the estrobolome that reactivate estrogens</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">294</span>(<span class="ref-number">49</span>):    p. <span class="ref-page">18586&ndash;18599</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra119.010950">http://doi.org/10.1074/jbc.ra119.010950</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">177.</div>
    <div class="ref-body">
    <span class="ref-author">Fontenele, R.S., C. Lacorte, N.S. Lamas, K. Schmidlin, A. Varsani, S.G. Ribeiro</span>,
    <span class="ref-title">Single Stranded DNA Viruses Associated with Capybara Faeces Sampled in Brazil</span>.
    <span class="ref-pub">Viruses,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">710</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/v11080710">http://doi.org/10.3390/v11080710</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">178.</div>
    <div class="ref-body">
    <span class="ref-author">Gama, S.R., M. Vogt, T. Kalina, K. Hupp, F. Hammerschmidt, K. Pallitsch, D.L. Zechel</span>,
    <span class="ref-title">An Oxidative Pathway for Microbial Utilization of Methylphosphonic Acid as a Phosphate Source</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">735&ndash;741</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.9b00024">http://doi.org/10.1021/acschembio.9b00024</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">179.</div>
    <div class="ref-body">
    <span class="ref-author">Ganley, J.G., H.K. D'Ambrosio, M. Shieh, E.R. Derbyshire</span>,
    <span class="ref-title">Coculturing of Mosquito-Microbiome Bacteria Promotes Heme Degradation in Elizabethkingia anophelis</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">21</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1279&ndash;1284</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201900675">http://doi.org/10.1002/cbic.201900675</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">180.</div>
    <div class="ref-body">
    <span class="ref-author">Giessen, T.W., B.J. Orlando, A.A. Verdegaal, M.G. Chambers, J. Gardener, D.C. Bell, G. Birrane, M. Liao, P.A. Silver</span>,
    <span class="ref-title">Large protein organelles form a new iron sequestration system with high storage capacity</span>.
    <span class="ref-pub">eLife,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">8</span>:.    <span class="ref-url"><a href="http://doi.org/10.7554/elife.46070">http://doi.org/10.7554/elife.46070</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">181.</div>
    <div class="ref-body">
    <span class="ref-author">Gomez-Escribano, J.P., J.F. Castro, V. Razmilic, S.A. Jarmusch, G. Saalbach, R. Ebel, M. Jaspars, B. Andrews, J.A. Asenjo, M.J. Bibb</span>,
    <span class="ref-title">Heterologous Expression of a Cryptic Gene Cluster from Streptomyces leeuwenhoekii C34T Yields a Novel Lasso Peptide, Leepeptin</span>.
    <span class="ref-pub">Applied and Environmental Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">85</span>(<span class="ref-number">23</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/aem.01752-19">http://doi.org/10.1128/aem.01752-19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">182.</div>
    <div class="ref-body">
    <span class="ref-author">Gonz&aacute;lez, J.M., L. Hern&aacute;ndez, I. Manzano, C. Pedr&oacute;s-Ali&oacute;</span>,
    <span class="ref-title">Functional annotation of orthologs in metagenomes: a case study of genes for the transformation of oceanic dimethylsulfoniopropionate</span>.
    <span class="ref-pub">The ISME Journal,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">5</span>):    p. <span class="ref-page">1183&ndash;1197</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41396-019-0347-6">http://doi.org/10.1038/s41396-019-0347-6</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">183.</div>
    <div class="ref-body">
    <span class="ref-author">Gumkowski, J.D., R.J. Martinie, P.S. Corrigan, J. Pan, M.R. Bauerle, M. Almarei, S.J. Booker, A. Silakov, C. Krebs, A.K. Boal</span>,
    <span class="ref-title">Analysis of RNA Methylation by Phylogenetically Diverse Cfr Radical <i>S</i>-Adenosylmethionine Enzymes Reveals an Iron-Binding Accessory Domain in a Clostridial Enzyme</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">29</span>):    p. <span class="ref-page">3169&ndash;3184</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00197">http://doi.org/10.1021/acs.biochem.9b00197</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">184.</div>
    <div class="ref-body">
    <span class="ref-author">Guo, J., M.A. Higgins, P. Daniel-Ivad, K.S. Ryan</span>,
    <span class="ref-title">An Asymmetric Reductase That Intercepts Acyclic Imino Acids Produced in Situ by a Partner Oxidase</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">12258&ndash;12267</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b03307">http://doi.org/10.1021/jacs.9b03307</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">185.</div>
    <div class="ref-body">
    <span class="ref-author">Hager, F.F., L. S&uuml;tzl, C. Stefanovi&cacute;, M. Blaukopf, C. Sch&auml;ffer</span>,
    <span class="ref-title">Pyruvate Substitutions on Glycoconjugates</span>.
    <span class="ref-pub">International Journal of Molecular Sciences,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">20</span>(<span class="ref-number">19</span>):    p. <span class="ref-page">4929</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/ijms20194929">http://doi.org/10.3390/ijms20194929</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">186.</div>
    <div class="ref-body">
    <span class="ref-author">Hai, Y., A.M. Huang, Y. Tang</span>,
    <span class="ref-title">Structure-guided function discovery of an NRPS-like glycine betaine reductase for choline biosynthesis in fungi</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">116</span>(<span class="ref-number">21</span>):    p. <span class="ref-page">10348&ndash;10353</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1903282116">http://doi.org/10.1073/pnas.1903282116</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">187.</div>
    <div class="ref-body">
    <span class="ref-author">Hangasky, J.A., T.C. Detomasi, M.A. Marletta</span>,
    <span class="ref-title">Glycosidic Bond Hydroxylation by Polysaccharide Monooxygenases</span>.
    <span class="ref-pub">Trends in Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">1</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">198&ndash;209</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.trechm.2019.01.007">http://doi.org/10.1016/j.trechm.2019.01.007</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">188.</div>
    <div class="ref-body">
    <span class="ref-author">Harrison, A.O., R.M. Moore, S.W. Polson, K.E. Wommack</span>,
    <span class="ref-title">Reannotation of the Ribonucleotide Reductase in a Cyanophage Reveals Life History Strategies Within the Virioplankton</span>.
    <span class="ref-pub">Frontiers in Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fmicb.2019.00134">http://doi.org/10.3389/fmicb.2019.00134</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">189.</div>
    <div class="ref-body">
    <span class="ref-author">Helfrich, E.J.N., R. Ueoka, A. Dolev, M. Rust, R.A. Meoded, A. Bhushan, G. Califano, R. Costa, M. Gugger, C. Steinbeck, P. Moreno, J. Piel</span>,
    <span class="ref-title">Automated structure prediction of trans-acyltransferase polyketide synthase products</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">15</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">813&ndash;821</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41589-019-0313-7">http://doi.org/10.1038/s41589-019-0313-7</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">190.</div>
    <div class="ref-body">
    <span class="ref-author">Hepowit, N.L., J.A. Maupin-Furlow</span>,
    <span class="ref-title">Rhodanese-Like Domain Protein UbaC and Its Role in Ubiquitin-Like Protein Modification and Sulfur Mobilization in Archaea</span>.
    <span class="ref-pub">Journal of Bacteriology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">201</span>(<span class="ref-number">15</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/jb.00254-19">http://doi.org/10.1128/jb.00254-19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">191.</div>
    <div class="ref-body">
    <span class="ref-author">Hermenau, R., J.L. Mehl, K. Ishida, B. Dose, S.J. Pidot, T.P. Stinear, C. Hertweck</span>,
    <span class="ref-title">Genomics-Driven Discovery of NO-Donating Diazeniumdiolate Siderophores in Diverse Plant-Associated Bacteria</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">37</span>):    p. <span class="ref-page">13024&ndash;13029</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201906326">http://doi.org/10.1002/anie.201906326</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">192.</div>
    <div class="ref-body">
    <span class="ref-author">Huddleston, J.P., F.M. Raushel</span>,
    <span class="ref-title">Biosynthesis of GDP-d-glycero-&alpha;-d-manno-heptose for the Capsular Polysaccharide of Campylobacter jejuni</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">37</span>):    p. <span class="ref-page">3893&ndash;3902</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00548">http://doi.org/10.1021/acs.biochem.9b00548</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">193.</div>
    <div class="ref-body">
    <span class="ref-author">Huddleston, J.P., F.M. Raushel</span>,
    <span class="ref-title">Functional Characterization of YdjH, a Sugar Kinase of Unknown Specificity in Escherichia coli K12</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">3354&ndash;3364</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00327">http://doi.org/10.1021/acs.biochem.9b00327</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">194.</div>
    <div class="ref-body">
    <span class="ref-author">Huddleston, J.P., J.B. Thoden, B.J. Dopkins, T. Narindoshvili, B.J. Fose, H.M. Holden, F.M. Raushel</span>,
    <span class="ref-title">Structural and Functional Characterization of YdjI, an Aldolase of Unknown Specificity in Escherichia coli K12</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">3340&ndash;3353</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00326">http://doi.org/10.1021/acs.biochem.9b00326</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">195.</div>
    <div class="ref-body">
    <span class="ref-author">Hudson, G.A., B.J. Burkhart, A.J. DiCaprio, C.J. Schwalen, B. Kille, T.V. Pogorelov, D.A. Mitchell</span>,
    <span class="ref-title">Bioinformatic Mapping of Radical <i>S</i>-Adenosylmethionine-Dependent Ribosomally Synthesized and Post-Translationally Modified Peptides Identifies New C&alpha;, C&beta;, and C&gamma;-Linked Thioether-Containing Peptides</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b01519">http://doi.org/10.1021/jacs.9b01519</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">196.</div>
    <div class="ref-body">
    <span class="ref-author">Hutinet, G., W. Kot, L. Cui, R. Hillebrand, S. Balamkundu, S. Gnanakalai, R. Neelakandan, A.B. Carstens, C.F. Lui, D. Tremblay, D. Jacobs-Sera, M. Sassanfar, Y.-J. Lee, P. Weigele, S. Moineau, G.F. Hatfull, P.C. Dedon, L.H. Hansen, V. de Cr&eacute;cy-Lagard</span>,
    <span class="ref-title">7-Deazaguanine modifications protect phage DNA from host restriction systems</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-019-13384-y">http://doi.org/10.1038/s41467-019-13384-y</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">197.</div>
    <div class="ref-body">
    <span class="ref-author">Jaroensuk, J., P. Intasian, C. Kiattisewee, P. Munkajohnpon, P. Chunthaboon, S. Buttranon, D. Trisrivirat, T. Wongnate, S. Maenpuen, R. Tinikul, P. Chaiyen</span>,
    <span class="ref-title">Addition of formate dehydrogenase increases the production of renewable alkane from an engineered metabolic pathway</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">294</span>(<span class="ref-number">30</span>):    p. <span class="ref-page">11536&ndash;11548</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra119.008246">http://doi.org/10.1074/jbc.ra119.008246</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">198.</div>
    <div class="ref-body">
    <span class="ref-author">Ji, X., T. Mo, W.-Q. Liu, W. Ding, Z. Deng, Q. Zhang</span>,
    <span class="ref-title">Revisiting the Mechanism of the Anaerobic Coproporphyrinogen&nbsp;III Oxidase HemN</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">19</span>):    p. <span class="ref-page">6235&ndash;6238</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201814708">http://doi.org/10.1002/anie.201814708</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">199.</div>
    <div class="ref-body">
    <span class="ref-author">Ji, Z.-Y., Q.-Y. Nie, Y. Yin, M. Zhang, H.-X. Pan, X.-F. Hou, G.-L. Tang</span>,
    <span class="ref-title">Activation and Characterization of Cryptic Gene Cluster: Two Series of Aromatic Polyketides Biosynthesized by Divergent Pathways</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">50</span>):    p. <span class="ref-page">18046&ndash;18054</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201910882">http://doi.org/10.1002/anie.201910882</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">200.</div>
    <div class="ref-body">
    <span class="ref-author">Jia, B., D. Yuan, W.J. Lan, Y.H. Xuan, C.O. Jeon</span>,
    <span class="ref-title">New insight into the classification and evolution of glucose transporters in the Metazoa</span>.
    <span class="ref-pub">The FASEB Journal,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">33</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">7519&ndash;7528</span>.    <span class="ref-url"><a href="http://doi.org/10.1096/fj.201802617r">http://doi.org/10.1096/fj.201802617r</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">201.</div>
    <div class="ref-body">
    <span class="ref-author">Khavrutskii, I.V., J.R. Compton, K.M. Jurkouich, P.M. Legler</span>,
    <span class="ref-title">Paired Carboxylic Acids in Enzymes and Their Role in Selective Substrate Binding, Catalysis, and Unusually Shifted pKa Values</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">52</span>):    p. <span class="ref-page">5351&ndash;5365</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00429">http://doi.org/10.1021/acs.biochem.9b00429</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">202.</div>
    <div class="ref-body">
    <span class="ref-author">Kraberger, S., K. Schmidlin, R.S. Fontenele, M. Walters, A. Varsani</span>,
    <span class="ref-title">Unravelling the Single-Stranded DNA Virome of the New Zealand Blackfly</span>.
    <span class="ref-pub">Viruses,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">532</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/v11060532">http://doi.org/10.3390/v11060532</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">203.</div>
    <div class="ref-body">
    <span class="ref-author">Lee, S., J. Kang, J. Kim</span>,
    <span class="ref-title">Structural and biochemical characterization of Rv0187, an O-methyltransferase from Mycobacterium tuberculosis</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41598-019-44592-7">http://doi.org/10.1038/s41598-019-44592-7</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">204.</div>
    <div class="ref-body">
    <span class="ref-author">Lefeuvre, P., D.P. Martin, S.F. Elena, D.N. Shepherd, P. Roumagnac, A. Varsani</span>,
    <span class="ref-title">Evolution and ecology of plant viruses</span>.
    <span class="ref-pub">Nature Reviews Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">632&ndash;644</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41579-019-0232-3">http://doi.org/10.1038/s41579-019-0232-3</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">205.</div>
    <div class="ref-body">
    <span class="ref-author">Li, B.-C., T. Zhang, Y.-Q. Li, G.-B. Ding</span>,
    <span class="ref-title">Target Discovery of Novel &alpha;-<small>L</small>-Rhamnosidases from Human Fecal Metagenome and Application for Biotransformation of Natural Flavonoid Glycosides</span>.
    <span class="ref-pub">Applied Biochemistry and Biotechnology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">189</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">1245&ndash;1261</span>.    <span class="ref-url"><a href="http://doi.org/10.1007/s12010-019-03063-5">http://doi.org/10.1007/s12010-019-03063-5</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">206.</div>
    <div class="ref-body">
    <span class="ref-author">Lin, G.-M., R. Warden-Rothman, C.A. Voigt</span>,
    <span class="ref-title">Retrosynthetic design of metabolic pathways to chemicals not found in nature</span>.
    <span class="ref-pub">Current Opinion in Systems Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>:    p. <span class="ref-page">82&ndash;107</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.coisb.2019.04.004">http://doi.org/10.1016/j.coisb.2019.04.004</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">207.</div>
    <div class="ref-body">
    <span class="ref-author">Liu, J., Z. Lin, Y. Li, Q. Zheng, D. Chen, W. Liu</span>,
    <span class="ref-title">Insights into the thioamidation of thiopeptins to enhance the understanding of the biosynthetic logic of thioamide-containing thiopeptides</span>.
    <span class="ref-pub">Organic & Biomolecular Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">15</span>):    p. <span class="ref-page">3727&ndash;3731</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c9ob00402e">http://doi.org/10.1039/c9ob00402e</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">208.</div>
    <div class="ref-body">
    <span class="ref-author">Liu, Y., L. Su, Q. Fang, J. Tabudravu, X. Yang, K. Rickaby, L. Trembleau, K. Kyeremeh, Z. Deng, H. Deng, Y. Yu</span>,
    <span class="ref-title">Enzymatic Reconstitution and Biosynthetic Investigation of the Bacterial Carbazole Neocarazostatin A</span>.
    <span class="ref-pub">The Journal of Organic Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">84</span>(<span class="ref-number">24</span>):    p. <span class="ref-page">16323&ndash;16328</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.joc.9b02688">http://doi.org/10.1021/acs.joc.9b02688</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">209.</div>
    <div class="ref-body">
    <span class="ref-author">Mahanta, N., K.A. Hicks, S. Naseem, Y. Zhang, D. Fedoseyenko, S.E. Ealick, T.P. Begley</span>,
    <span class="ref-title">Menaquinone Biosynthesis: Biochemical and Structural Studies of Chorismate Dehydratase</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">14</span>):    p. <span class="ref-page">1837&ndash;1840</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00105">http://doi.org/10.1021/acs.biochem.9b00105</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">210.</div>
    <div class="ref-body">
    <span class="ref-author">Malik, A., S.B. Kim</span>,
    <span class="ref-title">A comprehensive in silico analysis of sortase superfamily</span>.
    <span class="ref-pub">Journal of Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">57</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">431&ndash;443</span>.    <span class="ref-url"><a href="http://doi.org/10.1007/s12275-019-8545-5">http://doi.org/10.1007/s12275-019-8545-5</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">211.</div>
    <div class="ref-body">
    <span class="ref-author">Mandalapu, D., X. Ji, Q. Zhang</span>,
    <span class="ref-title">Reductive Cleavage of Sulfoxide and Sulfone by Two Radical <i>S</i>-Adenosyl-<small>L</small>-methionine Enzymes</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">36&ndash;39</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b00844">http://doi.org/10.1021/acs.biochem.8b00844</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">212.</div>
    <div class="ref-body">
    <span class="ref-author">Maresca, J.A., J.L. Keffer, P.P. Hempel, S.W. Polson, O. Shevchenko, J. Bhavsar, D. Powell, K.J. Miller, A. Singh, M.W. Hahn</span>,
    <span class="ref-title">Light Modulates the Physiology of Nonphototrophic Actinobacteria</span>.
    <span class="ref-pub">Journal of Bacteriology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">201</span>(<span class="ref-number">10</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/jb.00740-18">http://doi.org/10.1128/jb.00740-18</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">213.</div>
    <div class="ref-body">
    <span class="ref-author">Mo, T., H. Yuan, F. Wang, S. Ma, J. Wang, T. Li, G. Liu, S. Yu, X. Tan, W. Ding, Q. Zhang</span>,
    <span class="ref-title">Convergent evolution of the Cys decarboxylases involved in aminovinyl-cysteine (AviCys) biosynthesis</span>.
    <span class="ref-pub">FEBS Letters,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">593</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">573&ndash;580</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/1873-3468.13341">http://doi.org/10.1002/1873-3468.13341</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">214.</div>
    <div class="ref-body">
    <span class="ref-author">Morgan, G.L., A.M. Kretsch, K.C.S. Maria, S.J. Weeks, B. Li</span>,
    <span class="ref-title">Specificity of Nonribosomal Peptide Synthetases in the Biosynthesis of the Pseudomonas virulence factor</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">52</span>):    p. <span class="ref-page">5249&ndash;5254</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00360">http://doi.org/10.1021/acs.biochem.9b00360</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">215.</div>
    <div class="ref-body">
    <span class="ref-author">Morishita, Y., H. Zhang, T. Taniguchi, K. Mori, T. Asai</span>,
    <span class="ref-title">The Discovery of Fungal Polyene Macrolides via a Postgenomic Approach Reveals a Polyketide Macrocyclization by trans-Acting Thioesterase in Fungi</span>.
    <span class="ref-pub">Organic Letters,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">21</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">4788&ndash;4792</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.orglett.9b01674">http://doi.org/10.1021/acs.orglett.9b01674</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">216.</div>
    <div class="ref-body">
    <span class="ref-author">Mukherjee, K., J.P. Huddleston, T. Narindoshvili, V.V. Nemmara, F.M. Raushel</span>,
    <span class="ref-title">Functional Characterization of the ycjQRS Gene Cluster from Escherichia coli: A Novel Pathway for the Transformation of <small>D</small>-Gulosides to <small>D</small>-Glucosides</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">1388&ndash;1399</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b01278">http://doi.org/10.1021/acs.biochem.8b01278</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">217.</div>
    <div class="ref-body">
    <span class="ref-author">Neupane, D.P., S.H. Fullam, K.N. Chac&oacute;n, E.T. Yukl</span>,
    <span class="ref-title">Crystal structures of AztD provide mechanistic insights into direct zinc transfer between proteins</span>.
    <span class="ref-pub">Communications Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">2</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s42003-019-0542-z">http://doi.org/10.1038/s42003-019-0542-z</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">218.</div>
    <div class="ref-body">
    <span class="ref-author">Niehs, S.P., B. Dose, K. Scherlach, S.J. Pidot, T.P. Stinear, C. Hertweck</span>,
    <span class="ref-title">Genome Mining Reveals Endopyrroles from a Nonribosomal Peptide Assembly Line Triggered in Fungal&ndash;Bacterial Symbiosis</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">1811&ndash;1818</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.9b00406">http://doi.org/10.1021/acschembio.9b00406</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">219.</div>
    <div class="ref-body">
    <span class="ref-author">Peck, S.C., K. Denger, A. Burrichter, S.M. Irwin, E.P. Balskus, D. Schleheck</span>,
    <span class="ref-title">A glycyl radical enzyme enables hydrogen sulfide production by the human intestinal bacterium Bilophila wadsworthia</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">116</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">3171&ndash;3176</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1815661116">http://doi.org/10.1073/pnas.1815661116</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">220.</div>
    <div class="ref-body">
    <span class="ref-author">Pellock, S.J., W.G. Walton, S.M. Ervin, D. Torres-Rivera, B.C. Creekmore, G. Bergan, Z.D. Dunn, B. Li, A. Tripathy, M.R. Redinbo</span>,
    <span class="ref-title">Discovery and Characterization of FMN-Binding &beta;-Glucuronidases in the Human Gut Microbiome</span>.
    <span class="ref-pub">Journal of Molecular Biology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">431</span>(<span class="ref-number">5</span>):    p. <span class="ref-page">970&ndash;980</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.jmb.2019.01.013">http://doi.org/10.1016/j.jmb.2019.01.013</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">221.</div>
    <div class="ref-body">
    <span class="ref-author">Pellock, S.J., W.G. Walton, M.R. Redinbo</span>,
    <span class="ref-title">Selecting a Single Stereocenter: The Molecular Nuances That Differentiate &beta;-Hexuronidases in the Human Gut Microbiome</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1311&ndash;1317</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b01285">http://doi.org/10.1021/acs.biochem.8b01285</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">222.</div>
    <div class="ref-body">
    <span class="ref-author">Pongpamorn, P., P. Watthaisong, P. Pimviriyakul, A. Jaruwat, N. Lawan, P. Chitnumsub, P. Chaiyen</span>,
    <span class="ref-title">Identification of a Hotspot Residue for Improving the Thermostability of a Flavin-Dependent Monooxygenase</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">20</span>(<span class="ref-number">24</span>):    p. <span class="ref-page">3020&ndash;3031</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201900413">http://doi.org/10.1002/cbic.201900413</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">223.</div>
    <div class="ref-body">
    <span class="ref-author">Pyser, J.B., S.A.B. Dockrey, A.R. Ben&iacute;tez, L.A. Joyce, R.A. Wiscons, J.L. Smith, A.R.H. Narayan</span>,
    <span class="ref-title">Stereodivergent, Chemoenzymatic Synthesis of Azaphilone Natural Products</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">46</span>):    p. <span class="ref-page">18551&ndash;18559</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b09385">http://doi.org/10.1021/jacs.9b09385</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">224.</div>
    <div class="ref-body">
    <span class="ref-author">Radle, M.I., D.V. Miller, T.N. Laremore, S.J. Booker</span>,
    <span class="ref-title">Methanogenesis marker protein 10 (Mmp10) from Methanosarcina acetivorans is a radical <i>S</i>-adenosylmethionine methylase that unexpectedly requires cobalamin</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">294</span>(<span class="ref-number">31</span>):    p. <span class="ref-page">11712&ndash;11725</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra119.007609">http://doi.org/10.1074/jbc.ra119.007609</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">225.</div>
    <div class="ref-body">
    <span class="ref-author">Rajakovich, L.J., E.P. Balskus</span>,
    <span class="ref-title">Metabolic functions of the human gut microbiota: the role of metalloenzymes</span>.
    <span class="ref-pub">Natural Product Reports,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">36</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">593&ndash;625</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c8np00074c">http://doi.org/10.1039/c8np00074c</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">226.</div>
    <div class="ref-body">
    <span class="ref-author">Rajakovich, L.J., M.-E. Pandelia, A.J. Mitchell, W. Chang, B. Zhang, A.K. Boal, C. Krebs, J.M. Bollinger</span>,
    <span class="ref-title">A New Microbial Pathway for Organophosphonate Degradation Catalyzed by Two Previously Misannotated Non-Heme-Iron Oxygenases</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">12</span>):    p. <span class="ref-page">1627&ndash;1647</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00044">http://doi.org/10.1021/acs.biochem.9b00044</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">227.</div>
    <div class="ref-body">
    <span class="ref-author">Rizzolo, K., S.E. Cohen, A.C. Weitz, M.M.L. Mu&ntilde;oz, M.P. Hendrich, C.L. Drennan, S.J. Elliott</span>,
    <span class="ref-title">A widely distributed diheme enzyme from Burkholderia that displays an atypically stable bis-Fe(IV) state</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-019-09020-4">http://doi.org/10.1038/s41467-019-09020-4</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">228.</div>
    <div class="ref-body">
    <span class="ref-author">Ben&iacute;tez, A.R., A.R.H. Narayan</span>,
    <span class="ref-title">Frontiers in Biocatalysis: Profiling Function across Sequence Space</span>.
    <span class="ref-pub">ACS Central Science,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">5</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">1747&ndash;1749</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acscentsci.9b01112">http://doi.org/10.1021/acscentsci.9b01112</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">229.</div>
    <div class="ref-body">
    <span class="ref-author">Rolf, J., K. Rosenthal, S. Lütz</span>,
    <span class="ref-title">Application of Cell-Free Protein Synthesis for Faster Biocatalyst Development</span>.
    <span class="ref-pub">Catalysts,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">190</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/catal9020190">http://doi.org/10.3390/catal9020190</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">230.</div>
    <div class="ref-body">
    <span class="ref-author">Rose, H.R., A.O. Maggiolo, M.J. McBride, G.M. Palowitch, M.-E. Pandelia, K.M. Davis, N.H. Yennawar, A.K. Boal</span>,
    <span class="ref-title">Structures of Class Id Ribonucleotide Reductase Catalytic Subunits Reveal a Minimal Architecture for Deoxynucleotide Biosynthesis</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">14</span>):    p. <span class="ref-page">1845&ndash;1860</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b01252">http://doi.org/10.1021/acs.biochem.8b01252</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">231.</div>
    <div class="ref-body">
    <span class="ref-author">Scott, T.A., J. Piel</span>,
    <span class="ref-title">The hidden enzymology of bacterial natural product biosynthesis</span>.
    <span class="ref-pub">Nature Reviews Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">3</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">404&ndash;425</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41570-019-0107-1">http://doi.org/10.1038/s41570-019-0107-1</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">232.</div>
    <div class="ref-body">
    <span class="ref-author">Shi, J., C.L. Liu, B. Zhang, W.J. Guo, J. Zhu, C.-Y. Chang, E.J. Zhao, R.H. Jiao, R.X. Tan, H.M. Ge</span>,
    <span class="ref-title">Genome mining and biosynthesis of kitacinnamycins as a STING activator</span>.
    <span class="ref-pub">Chemical Science,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">18</span>):    p. <span class="ref-page">4839&ndash;4846</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c9sc00815b">http://doi.org/10.1039/c9sc00815b</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">233.</div>
    <div class="ref-body">
    <span class="ref-author">Sieow, B.F., T.J. Nurminen, H. Ling, M.W. Chang</span>,
    <span class="ref-title">Meta-Omics- and Metabolic Modeling-Assisted Deciphering of Human Microbiota Metabolism</span>.
    <span class="ref-pub">Biotechnology Journal,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">14</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1800445</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/biot.201800445">http://doi.org/10.1002/biot.201800445</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">234.</div>
    <div class="ref-body">
    <span class="ref-author">Sikandar, A., L. Franz, O. Melse, I. Antes, J. Koehnke</span>,
    <span class="ref-title">Thiazoline-Specific Amidohydrolase PurAH Is the Gatekeeper of Bottromycin Biosynthesis</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">25</span>):    p. <span class="ref-page">9748&ndash;9752</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b12231">http://doi.org/10.1021/jacs.8b12231</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">235.</div>
    <div class="ref-body">
    <span class="ref-author">Straub, K., M. Linde, C. Kropp, S. Blanquart, P. Babinger, R. Merkl</span>,
    <span class="ref-title">Sequence selection by FitSS4ASR alleviates ancestral sequence reconstruction as exemplified for geranylgeranylglyceryl phosphate synthase</span>.
    <span class="ref-pub">Biological Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">400</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">367&ndash;381</span>.    <span class="ref-url"><a href="http://doi.org/10.1515/hsz-2018-0344">http://doi.org/10.1515/hsz-2018-0344</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">236.</div>
    <div class="ref-body">
    <span class="ref-author">Sützl, L., G. Foley, E.M.J. Gillam, M. Bod&eacute;n, D. Haltrich</span>,
    <span class="ref-title">The GMC superfamily of oxidoreductases revisited: analysis and evolution of fungal GMC oxidoreductases</span>.
    <span class="ref-pub">Biotechnology for Biofuels,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s13068-019-1457-0">http://doi.org/10.1186/s13068-019-1457-0</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">237.</div>
    <div class="ref-body">
    <span class="ref-author">Ting, C.P., M.A. Funk, S.L. Halaby, Z. Zhang, T. Gonen, W.A. van der Donk</span>,
    <span class="ref-title">Use of a scaffold peptide in the biosynthesis of amino acid&ndash;derived natural products</span>.
    <span class="ref-pub">Science,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">365</span>(<span class="ref-number">6450</span>):    p. <span class="ref-page">280&ndash;284</span>.    <span class="ref-url"><a href="http://doi.org/10.1126/science.aau6232">http://doi.org/10.1126/science.aau6232</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">238.</div>
    <div class="ref-body">
    <span class="ref-author">Tong, Y., Y. Wei, Y. Hu, E.L. Ang, H. Zhao, Y. Zhang</span>,
    <span class="ref-title">A Pathway for Isethionate Dissimilation in Bacillus krulwichiae</span>.
    <span class="ref-pub">Applied and Environmental Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">85</span>(<span class="ref-number">15</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/aem.00793-19">http://doi.org/10.1128/aem.00793-19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">239.</div>
    <div class="ref-body">
    <span class="ref-author">Travis, S., M.R. Shay, S. Manabe, N.C. Gilbert, P.A. Frantom, M.K. Thompson</span>,
    <span class="ref-title">Characterization of the genomically encoded fosfomycin resistance enzyme from Mycobacterium abscessus</span>.
    <span class="ref-pub">MedChemComm,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">11</span>):    p. <span class="ref-page">1948&ndash;1957</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c9md00372j">http://doi.org/10.1039/c9md00372j</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">240.</div>
    <div class="ref-body">
    <span class="ref-author">Tsui, H.S., N.V.B. Pham, B.R. Amer, M.C. Bradley, J.E. Gosschalk, M. Gallagher-Jones, H. Ibarra, R.T. Clubb, C.E. Blaby-Haas, C.F. Clarke</span>,
    <span class="ref-title">Human COQ10A and COQ10B are distinct lipid-binding START domain proteins required for coenzyme Q function</span>.
    <span class="ref-pub">Journal of Lipid Research,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">60</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">1293&ndash;1310</span>.    <span class="ref-url"><a href="http://doi.org/10.1194/jlr.m093534">http://doi.org/10.1194/jlr.m093534</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">241.</div>
    <div class="ref-body">
    <span class="ref-author">Uma&ntilde;a, A., B.E. Sanders, C.C. Yoo, M.A. Casasanta, B. Udayasuryan, S.S. Verbridge, D.J. Slade</span>,
    <span class="ref-title">Utilizing Whole Fusobacterium Genomes To Identify, Correct, and Characterize Potential Virulence Protein Families</span>.
    <span class="ref-pub">Journal of Bacteriology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">201</span>(<span class="ref-number">23</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/jb.00273-19">http://doi.org/10.1128/jb.00273-19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">242.</div>
    <div class="ref-body">
    <span class="ref-author">Vu, V.V., J.A. Hangasky, T.C. Detomasi, S.J.W. Henry, S.T. Ngo, E.A. Span, M.A. Marletta</span>,
    <span class="ref-title">Substrate selectivity in starch polysaccharide monooxygenases</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">294</span>(<span class="ref-number">32</span>):    p. <span class="ref-page">12157&ndash;12166</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra119.009509">http://doi.org/10.1074/jbc.ra119.009509</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">243.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, Y., I. Shin, Y. Fu, K.L. Colabroy, A. Liu</span>,
    <span class="ref-title">Crystal Structures of L-DOPA Dioxygenase from Streptomyces sclerotialus</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">52</span>):    p. <span class="ref-page">5339&ndash;5350</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00396">http://doi.org/10.1021/acs.biochem.9b00396</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">244.</div>
    <div class="ref-body">
    <span class="ref-author">Wu, Y., R. Wu, D. Mandalapu, X. Ji, T. Chen, W. Ding, Q. Zhang</span>,
    <span class="ref-title">Radical SAM-dependent adenosylation catalyzed by <small>L</small>-tyrosine lyases</span>.
    <span class="ref-pub">Organic & Biomolecular Chemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">17</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">1809&ndash;1812</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c8ob02906g">http://doi.org/10.1039/c8ob02906g</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">245.</div>
    <div class="ref-body">
    <span class="ref-author">Xing, M., Y. Wei, Y. Zhou, J. Zhang, L. Lin, Y. Hu, G. Hua, A.N.N. Urs, D. Liu, F. Wang, C. Guo, Y. Tong, M. Li, Y. Liu, E.L. Ang, H. Zhao, Z. Yuchi, Y. Zhang</span>,
    <span class="ref-title">Radical-mediated C-S bond cleavage in C2 sulfonate degradation by anaerobic bacteria</span>.
    <span class="ref-pub">Nature Communications,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41467-019-09618-8">http://doi.org/10.1038/s41467-019-09618-8</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">246.</div>
    <div class="ref-body">
    <span class="ref-author">Yin, L., C.S. Harwood</span>,
    <span class="ref-title">Functional divergence of annotated <small>L</small>-isoaspartate O-methyltransferases in an &alpha;-proteobacterium</span>.
    <span class="ref-pub">Journal of Biological Chemistry,</span>
    <span class="ref-year">2018</span>.
    <span class="ref-volume">294</span>(<span class="ref-number">8</span>):    p. <span class="ref-page">2854&ndash;2861</span>.    <span class="ref-url"><a href="http://doi.org/10.1074/jbc.ra118.006546">http://doi.org/10.1074/jbc.ra118.006546</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">247.</div>
    <div class="ref-body">
    <span class="ref-author">You, J., S. Lin, T. Jiang</span>,
    <span class="ref-title">Origins and Evolution of the &alpha;-L-Fucosidases: From Bacteria to Metazoans</span>.
    <span class="ref-pub">Frontiers in Microbiology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">10</span>:.    <span class="ref-url"><a href="http://doi.org/10.3389/fmicb.2019.01756">http://doi.org/10.3389/fmicb.2019.01756</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">248.</div>
    <div class="ref-body">
    <span class="ref-author">Yuan, Y., R. Zallot, T.L. Grove, D.J. Payan, I. Martin-Verstraete, S. &Scaron;epi&cacute;, S. Balamkundu, R. Neelakandan, V.K. Gadi, C.-F. Liu, M.A. Swairjo, P.C. Dedon, S.C. Almo, J.A. Gerlt, V. de Cr&eacute;cy-Lagard</span>,
    <span class="ref-title">Discovery of novel bacterial queuine salvage enzymes and pathways in human pathogens</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">116</span>(<span class="ref-number">38</span>):    p. <span class="ref-page">19126&ndash;19135</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1909604116">http://doi.org/10.1073/pnas.1909604116</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">249.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, D., F. Zhang, W. Liu</span>,
    <span class="ref-title">A KAS-III Heterodimer in Lipstatin Biosynthesis Nondecarboxylatively Condenses C8 and C14 Fatty Acyl-CoA Substrates by a Variable Mechanism during the Establishment of a C22 Aliphatic Skeleton</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">141</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">3993&ndash;4001</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.8b12843">http://doi.org/10.1021/jacs.8b12843</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">250.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, R., X. Xu, H. Cao, C. Yuan, Y. Yuminaga, S. Zhao, J. Shi, B. Zhang</span>,
    <span class="ref-title">Purification, characterization, and application of a high activity 3-ketosteroid-&Delta;1-dehydrogenase from Mycobacterium neoaurum DSM 1381</span>.
    <span class="ref-pub">Applied Microbiology and Biotechnology,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">103</span>(<span class="ref-number">16</span>):    p. <span class="ref-page">6605&ndash;6616</span>.    <span class="ref-url"><a href="http://doi.org/10.1007/s00253-019-09988-5">http://doi.org/10.1007/s00253-019-09988-5</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">251.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, Y., C.E. Blaby-Haas, S. Steimle, A.F. Verissimo, V.A. Garcia-Angulo, H.-G. Koch, F. Daldal, B. Khalfaoui-Hassani</span>,
    <span class="ref-title">Cu Transport by the Extended Family of CcoA-like Transporters (CalT) in Proteobacteria</span>.
    <span class="ref-pub">Scientific Reports,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">9</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1038/s41598-018-37988-4">http://doi.org/10.1038/s41598-018-37988-4</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">252.</div>
    <div class="ref-body">
    <span class="ref-author">Zhi, Y., T. Narindoshvili, L. Bogomolnaya, M. Talamantes, A.E. Saadi, H. Andrews-Polymenis, F.M. Raushel</span>,
    <span class="ref-title">Deciphering the Enzymatic Function of the Bovine Enteric Infection-Related Protein YfeJ from Salmonella enterica Serotype Typhimurium</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">9</span>):    p. <span class="ref-page">1236&ndash;1245</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.8b01283">http://doi.org/10.1021/acs.biochem.8b01283</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">253.</div>
    <div class="ref-body">
    <span class="ref-author">Zhou, Y., Y. Wei, L. Lin, T. Xu, E.L. Ang, H. Zhao, Z. Yuchi, Y. Zhang</span>,
    <span class="ref-title">Biochemical and structural investigation of sulfoacetaldehyde reductase from Klebsiella oxytoca</span>.
    <span class="ref-pub">Biochemical Journal,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">476</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">733&ndash;746</span>.    <span class="ref-url"><a href="http://doi.org/10.1042/bcj20190005">http://doi.org/10.1042/bcj20190005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">254.</div>
    <div class="ref-body">
    <span class="ref-author">Zwick, C.R., M.B. Sosa, H. Renata</span>,
    <span class="ref-title">Characterization of a Citrulline 4-Hydroxylase from Nonribosomal Peptide GE81112 Biosynthesis and Engineering of Its Substrate Specificity for the Chemoenzymatic Synthesis of Enduracididine</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">58</span>(<span class="ref-number">52</span>):    p. <span class="ref-page">18854&ndash;18858</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201910659">http://doi.org/10.1002/anie.201910659</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">255.</div>
    <div class="ref-body">
    <span class="ref-author">Athukoralage, J.S., S.A. McMahon, C. Zhang, S. Grüschow, S. Graham, M. Krupovic, R.J. Whitaker, T.M. Gloster, M.F. White</span>,
    <span class="ref-title">An anti-CRISPR viral ring nuclease subverts type III CRISPR immunity</span>.
    <span class="ref-pub">Nature,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">577</span>(<span class="ref-number">7791</span>):    p. <span class="ref-page">572&ndash;575</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41586-019-1909-5">http://doi.org/10.1038/s41586-019-1909-5</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">256.</div>
    <div class="ref-body">
    <span class="ref-author">Bösch, N., B. Mariana, U. Greczmiel, B. Morinaka, M. Gugger, A. Oxenius, A.L. Vagstad, J. Piel</span>,
    <span class="ref-title">Landornamides, antiviral ornithine-containing ribosomal peptides discovered by proteusin mining</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201916321">http://doi.org/10.1002/anie.201916321</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">257.</div>
    <div class="ref-body">
    <span class="ref-author">Caetano, T., W. van der Donk, S. Mendo</span>,
    <span class="ref-title">Bacteroidetes can be a rich source of novel lanthipeptides: The case study of Pedobacter lusitanus</span>.
    <span class="ref-pub">Microbiological Research,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">235</span>:    p. <span class="ref-page">126441</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.micres.2020.126441">http://doi.org/10.1016/j.micres.2020.126441</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">258.</div>
    <div class="ref-body">
    <span class="ref-author">Canu, N., M. Moutiez, P. Belin, M. Gondry</span>,
    <span class="ref-title">Cyclodipeptide synthases: a promising biotechnological tool for the synthesis of diverse 2,5-diketopiperazines</span>.
    <span class="ref-pub">Natural Product Reports,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">37</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">312&ndash;321</span>.    <span class="ref-url"><a href="http://doi.org/10.1039/c9np00036d">http://doi.org/10.1039/c9np00036d</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">259.</div>
    <div class="ref-body">
    <span class="ref-author">Dhawi, F.</span>,
    <span class="ref-title">Plant Growth Promoting Rhizobacteria (PGPR) Regulated Phyto and Microbial Beneficial Protein Interactions</span>.
    <span class="ref-pub">Open Life Sciences,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">15</span>(<span class="ref-number">1</span>):    p. <span class="ref-page">68&ndash;78</span>.    <span class="ref-url"><a href="http://doi.org/10.1515/biol-2020-0008">http://doi.org/10.1515/biol-2020-0008</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">260.</div>
    <div class="ref-body">
    <span class="ref-author">Ding, W., X. Ji, Y. Zhong, K. Xu, Q. Zhang</span>,
    <span class="ref-title">Adenosylation reactions catalyzed by the radical <i>S</i>-adenosylmethionine superfamily enzymes</span>.
    <span class="ref-pub">Current Opinion in Chemical Biology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">55</span>:    p. <span class="ref-page">86&ndash;95</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.cbpa.2020.01.007">http://doi.org/10.1016/j.cbpa.2020.01.007</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">261.</div>
    <div class="ref-body">
    <span class="ref-author">Dunbar, K.L., M. Dell, F. Gude, C. Hertweck</span>,
    <span class="ref-title">Reconstitution of polythioamide antibiotic backbone formation reveals unusual thiotemplated assembly strategy</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">117</span>(<span class="ref-number">16</span>):    p. <span class="ref-page">8850&ndash;8858</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1918759117">http://doi.org/10.1073/pnas.1918759117</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">262.</div>
    <div class="ref-body">
    <span class="ref-author">Esch, R., R. Merkl</span>,
    <span class="ref-title">Conserved genomic neighborhood is a strong but no perfect indicator for a direct interaction of microbial gene products</span>.
    <span class="ref-pub">BMC Bioinformatics,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">21</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s12859-019-3200-z">http://doi.org/10.1186/s12859-019-3200-z</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">263.</div>
    <div class="ref-body">
    <span class="ref-author">Fang, Q., F. Maglangit, L. Wu, R. Ebel, K. Kyeremeh, J.H. Andersen, F. Annang, G. P&eacute;rez-Moreno, F. Reyes, H. Deng</span>,
    <span class="ref-title">Signalling and Bioactive Metabolites from Streptomyces sp. RK44</span>.
    <span class="ref-pub">Molecules,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">25</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">460</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/molecules25030460">http://doi.org/10.3390/molecules25030460</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">264.</div>
    <div class="ref-body">
    <span class="ref-author">Fontenele, R.S., A.M. Salywon, L.C. Majure, I.N. Cobb, A. Bhaskara, J.A. Avalos-Calleros, G.R. Argüello-Astorga, K. Schmidlin, A. Khalifeh, K. Smith, J. Schreck, M.C. Lund, M. Köhler, M.F. Wojciechowski, W.C. Hodgson, R. Puente-Martinez, K.V. Doorslaer, S. Kumari, C. Verni&egrave;re, D. Filloux, P. Roumagnac, P. Lefeuvre, S.G. Ribeiro, S. Kraberger, D.P. Martin, A. Varsani</span>,
    <span class="ref-title">A Novel Divergent Geminivirus Identified in Asymptomatic New World Cactaceae Plants</span>.
    <span class="ref-pub">Viruses,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">398</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/v12040398">http://doi.org/10.3390/v12040398</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">265.</div>
    <div class="ref-body">
    <span class="ref-author">Ghebreamlak, S.M., S.O. Mansoorabadi</span>,
    <span class="ref-title">Divergent Members of the Nitrogenase Superfamily: Tetrapyrrole Biosynthesis and Beyond</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.201900782">http://doi.org/10.1002/cbic.201900782</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">266.</div>
    <div class="ref-body">
    <span class="ref-author">G&oacute;recki, K., M.M. McEvoy</span>,
    <span class="ref-title">Phylogenetic analysis reveals an ancient gene duplication as the origin of the MdtABC efflux pump</span>.
    <span class="ref-pub">PLOS ONE,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">15</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">e0228877</span>.    <span class="ref-url"><a href="http://doi.org/10.1371/journal.pone.0228877">http://doi.org/10.1371/journal.pone.0228877</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">267.</div>
    <div class="ref-body">
    <span class="ref-author">Huang, J.-Q., X. Fang, X. Tian, P. Chen, J.-L. Lin, X.-X. Guo, J.-X. Li, Z. Fan, W.-M. Song, F.-Y. Chen, R. Ahati, L.-J. Wang, Q. Zhao, C. Martin, X.-Y. Chen</span>,
    <span class="ref-title">Aromatization of natural products by a specialized detoxification enzyme</span>.
    <span class="ref-pub">Nature Chemical Biology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">16</span>(<span class="ref-number">3</span>):    p. <span class="ref-page">250&ndash;256</span>.    <span class="ref-url"><a href="http://doi.org/10.1038/s41589-019-0446-8">http://doi.org/10.1038/s41589-019-0446-8</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">268.</div>
    <div class="ref-body">
    <span class="ref-author">Huddleston, J.P., F.M. Raushel</span>,
    <span class="ref-title">Functional Characterization of Cj1427, a Unique Ping-Pong Dehydrogenase Responsible for the Oxidation of GDP-d-glycero-&alpha;-d-manno-heptose in Campylobacter jejuni</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">59</span>(<span class="ref-number">13</span>):    p. <span class="ref-page">1328&ndash;1337</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.0c00097">http://doi.org/10.1021/acs.biochem.0c00097</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">269.</div>
    <div class="ref-body">
    <span class="ref-author">Jeoung, J.-H., B.M. Martins, H. Dobbek</span>,
    <span class="ref-title">Double-Cubane [8Fe9S] Clusters: A Novel Nitrogenase-Related Cofactor in Biology</span>.
    <span class="ref-pub">ChemBioChem,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-url"><a href="http://doi.org/10.1002/cbic.202000016">http://doi.org/10.1002/cbic.202000016</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">270.</div>
    <div class="ref-body">
    <span class="ref-author">Li, J., A. Amatuni, H. Renata</span>,
    <span class="ref-title">Recent advances in the chemoenzymatic synthesis of bioactive natural products</span>.
    <span class="ref-pub">Current Opinion in Chemical Biology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">55</span>:    p. <span class="ref-page">111&ndash;118</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.cbpa.2020.01.005">http://doi.org/10.1016/j.cbpa.2020.01.005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">271.</div>
    <div class="ref-body">
    <span class="ref-author">Mart&iacute;nez-Rodr&iacute;guez, S., P. Soriano-Maldonado, J.A. Gavira</span>,
    <span class="ref-title">N-succinylamino acid racemases: Enzymatic properties and biotechnological applications</span>.
    <span class="ref-pub">Biochimica et Biophysica Acta (BBA) - Proteins and Proteomics,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">1868</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">140377</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.bbapap.2020.140377">http://doi.org/10.1016/j.bbapap.2020.140377</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">272.</div>
    <div class="ref-body">
    <span class="ref-author">Mukhopadhyay, R., K.N. Chac&oacute;n, J.M. Jarvis, M.R. Talipov, E.T. Yukl</span>,
    <span class="ref-title">Structural insights into the mechanism of oxidative activation of heme-free H-NOX from Vibrio cholerae</span>.
    <span class="ref-pub">Biochemical Journal,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">477</span>(<span class="ref-number">6</span>):    p. <span class="ref-page">1123&ndash;1136</span>.    <span class="ref-url"><a href="http://doi.org/10.1042/bcj20200124">http://doi.org/10.1042/bcj20200124</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">273.</div>
    <div class="ref-body">
    <span class="ref-author">Orton, J.P., M. Morales, R.S. Fontenele, K. Schmidlin, S. Kraberger, D.J. Leavitt, T.H. Webster, M.A. Wilson, K. Kusumi, G.A. Dolby, A. Varsani</span>,
    <span class="ref-title">Virus Discovery in Desert Tortoise Fecal Samples: Novel Circular Single-Stranded DNA Viruses</span>.
    <span class="ref-pub">Viruses,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">12</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">143</span>.    <span class="ref-url"><a href="http://doi.org/10.3390/v12020143">http://doi.org/10.3390/v12020143</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">274.</div>
    <div class="ref-body">
    <span class="ref-author">Rajendran, A., K. Vaidya, J. Mendoza, J. Bridwell-Rabb, S.S. Kamat</span>,
    <span class="ref-title">Functional Annotation of ABHD14B, an Orphan Serine Hydrolase Enzyme</span>.
    <span class="ref-pub">Biochemistry,</span>
    <span class="ref-year">2019</span>.
    <span class="ref-volume">59</span>(<span class="ref-number">2</span>):    p. <span class="ref-page">183&ndash;196</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/acs.biochem.9b00703">http://doi.org/10.1021/acs.biochem.9b00703</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">275.</div>
    <div class="ref-body">
    <span class="ref-author">Rocker, A., J.A. Lacey, M.J. Belousoff, J.J. Wilksch, R.A. Strugnell, M.R. Davies, T. Lithgow</span>,
    <span class="ref-title">Global Trends in Proteome Remodeling of the Outer Membrane Modulate Antimicrobial Permeability in Klebsiella pneumoniae</span>.
    <span class="ref-pub">mBio,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">11</span>(<span class="ref-number">2</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/mbio.00603-20">http://doi.org/10.1128/mbio.00603-20</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">276.</div>
    <div class="ref-body">
    <span class="ref-author">Sekula, B., M. Ruszkowski, Z. Dauter</span>,
    <span class="ref-title">S-adenosylmethionine synthases in plants: Structural characterization of type I and II isoenzymes from Arabidopsis thaliana and Medicago truncatula</span>.
    <span class="ref-pub">International Journal of Biological Macromolecules,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">151</span>:    p. <span class="ref-page">554&ndash;565</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.ijbiomac.2020.02.100">http://doi.org/10.1016/j.ijbiomac.2020.02.100</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">277.</div>
    <div class="ref-body">
    <span class="ref-author">Stack, T.M.M., K.N. Morrison, T.M. Dettmer, B. Wille, C. Kim, R. Joyce, M. Jermain, Y.T. Naing, K. Bhatti, B. San Francisco, M.S. Carter, J.A. Gerlt</span>,
    <span class="ref-title">Characterization of an <small>L</small>-Ascorbate Catabolic Pathway with Unprecedented Enzymatic Transformations</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">142</span>(<span class="ref-number">4</span>):    p. <span class="ref-page">1657&ndash;1661</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b09863">http://doi.org/10.1021/jacs.9b09863</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">278.</div>
    <div class="ref-body">
    <span class="ref-author">Steiningerova, L., Z. Kamenik, R. Gazak, S. Kadlcik, G. Bashiri, P. Man, M. Kuzma, M. Pavlikova, J. Janata</span>,
    <span class="ref-title">Different Reaction Specificities of F420H2-Dependent Reductases Facilitate Pyrrolobenzodiazepines and Lincomycin To Fit Their Biological Targets</span>.
    <span class="ref-pub">Journal of the American Chemical Society,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">142</span>(<span class="ref-number">7</span>):    p. <span class="ref-page">3440&ndash;3448</span>.    <span class="ref-url"><a href="http://doi.org/10.1021/jacs.9b11234">http://doi.org/10.1021/jacs.9b11234</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">279.</div>
    <div class="ref-body">
    <span class="ref-author">Surger, M., A. Angelov, W. Liebl</span>,
    <span class="ref-title">Distribution and diversity of olefins and olefin-biosynthesis genes in Gram-positive bacteria</span>.
    <span class="ref-pub">Biotechnology for Biofuels,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">13</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1186/s13068-020-01706-y">http://doi.org/10.1186/s13068-020-01706-y</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">280.</div>
    <div class="ref-body">
    <span class="ref-author">Tararina, M.A., K.N. Allen</span>,
    <span class="ref-title">Bioinformatic Analysis of the Flavin-Dependent Amine Oxidase Superfamily: Adaptations for Substrate Specificity and Catalytic Diversity</span>.
    <span class="ref-pub">Journal of Molecular Biology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">432</span>(<span class="ref-number">10</span>):    p. <span class="ref-page">3269&ndash;3288</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.jmb.2020.03.007">http://doi.org/10.1016/j.jmb.2020.03.007</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">281.</div>
    <div class="ref-body">
    <span class="ref-author">Thierbach, S., P. Sartor, O. Yücel, S. Fetzner</span>,
    <span class="ref-title">Efficient modification of the Pseudomonas aeruginosa toxin 2-heptyl-1-hydroxyquinolin-4-one by three Bacillus glycosyltransferases with broad substrate ranges</span>.
    <span class="ref-pub">Journal of Biotechnology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">308</span>:    p. <span class="ref-page">74&ndash;81</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.jbiotec.2019.11.015">http://doi.org/10.1016/j.jbiotec.2019.11.015</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">282.</div>
    <div class="ref-body">
    <span class="ref-author">Ueoka, R., R.A. Meoded, A. Gran-Scheuch, A. Bhushan, M.W. Fraaije, J. Piel</span>,
    <span class="ref-title">Genome Mining of Oxidation Modules in</span>.
    <span class="ref-pub">Angewandte Chemie International Edition,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">59</span>(<span class="ref-number">20</span>):    p. <span class="ref-page">7761&ndash;7765</span>.    <span class="ref-url"><a href="http://doi.org/10.1002/anie.201916005">http://doi.org/10.1002/anie.201916005</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">283.</div>
    <div class="ref-body">
    <span class="ref-author">Waldern, J., N.J. Schiraldi, M. Belfort, O. Novikova</span>,
    <span class="ref-title">Bacterial Group II Intron Genomic Neighborhoods Reflect Survival Strategies: Hiding and Hijacking</span>.
    <span class="ref-pub">Molecular Biology and Evolution,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-url"><a href="http://doi.org/10.1093/molbev/msaa055">http://doi.org/10.1093/molbev/msaa055</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">284.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, B., F. Guo, C. Huang, H. Zhao</span>,
    <span class="ref-title">Unraveling the iterative type I polyketide synthases hidden in Streptomyces</span>.
    <span class="ref-pub">Proceedings of the National Academy of Sciences,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">117</span>(<span class="ref-number">15</span>):    p. <span class="ref-page">8449&ndash;8454</span>.    <span class="ref-url"><a href="http://doi.org/10.1073/pnas.1917664117">http://doi.org/10.1073/pnas.1917664117</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">285.</div>
    <div class="ref-body">
    <span class="ref-author">Wang, Z., A.S. Tauzin, E. Laville, P. Tedesco, F. L&eacute;tisse, N. Terrapon, P. Lepercq, M. Mercade, G. Potocki-Veronese</span>,
    <span class="ref-title">Harvesting of Prebiotic Fructooligosaccharides by Nonbeneficial Human Gut Bacteria</span>.
    <span class="ref-pub">mSphere,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">5</span>(<span class="ref-number">1</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/msphere.00771-19">http://doi.org/10.1128/msphere.00771-19</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">286.</div>
    <div class="ref-body">
    <span class="ref-author">Yun, B.-R., A. Malik, S.B. Kim</span>,
    <span class="ref-title">Genome based characterization of Kitasatospora sp. MMS16-BH015, a multiple heavy metal resistant soil actinobacterium with high antimicrobial potential</span>.
    <span class="ref-pub">Gene,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">733</span>:    p. <span class="ref-page">144379</span>.    <span class="ref-url"><a href="http://doi.org/10.1016/j.gene.2020.144379">http://doi.org/10.1016/j.gene.2020.144379</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">287.</div>
    <div class="ref-body">
    <span class="ref-author">Zhang, C., X. Chen, A. Orban, S. Shukal, F. Birk, H.-P. Too, M. Rühl</span>,
    <span class="ref-title">Agrocybe aegerita Serves As a Gateway for Identifying Sesquiterpene Biosynthetic Enzymes in Higher Fungi</span>.
    <span class="ref-pub">ACS Chemical Biology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-url"><a href="http://doi.org/10.1021/acschembio.0c00155">http://doi.org/10.1021/acschembio.0c00155</a></span>
    </div>
</div>
<div class="ref-group">
    <div class="ref-index">288.</div>
    <div class="ref-body">
    <span class="ref-author">Zhu, D., Y. Wei, J. Yin, D. Liu, E.L. Ang, H. Zhao, Y. Zhang</span>,
    <span class="ref-title">A Pathway for Degradation of Uracil to Acetyl Coenzyme A in Bacillus megaterium</span>.
    <span class="ref-pub">Applied and Environmental Microbiology,</span>
    <span class="ref-year">2020</span>.
    <span class="ref-volume">86</span>(<span class="ref-number">7</span>):.    <span class="ref-url"><a href="http://doi.org/10.1128/aem.02837-19">http://doi.org/10.1128/aem.02837-19</a></span>
    </div>
</div>

</div>
<?php include("inc/tab_footer.inc.php"); ?>


<?php require_once("inc/footer.inc.php"); ?>

