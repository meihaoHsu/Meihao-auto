<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* /setup/multi-currency.twig */
class __TwigTemplate_7c394458dadd961e64d7c2d9d639fb61312701d8135c1230ab2bdb495239169a extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<span id=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "step_id", []), "html", null, true);
        echo "\">
<h1>";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "heading", []), "html", null, true);
        echo "</h1>

<div><strong>";
        // line 4
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "description", []), "html", null, true);
        echo "</strong></div>

<ul>
    <li>";
        // line 7
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "bullet1", []), "html", null, true);
        echo "</li>
    <li>";
        // line 8
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "bullet2", []), "html", null, true);
        echo "</li>
    <li>";
        // line 9
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "bullet3", []), "html", null, true);
        echo "</li>
    <li><a target=\"_blank\" class=\"wpml-external-link\" rel=\"noopener\" href=\"";
        // line 10
        echo \WPML\Core\twig_escape_filter($this->env, ($context["documentation_url"] ?? null), "html", null, true);
        echo "\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "bullet4", []), "html", null, true);
        echo "</a></li>
</ul>

<p class=\"wcml-setup-actions step\">
    <a href=\"";
        // line 14
        echo \WPML\Core\twig_escape_filter($this->env, ($context["go_back_url"] ?? null), "html", null, true);
        echo "\" class=\"go-back\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "go_back", []), "html", null, true);
        echo "</a>
    <a href=\"";
        // line 15
        echo \WPML\Core\twig_escape_filter($this->env, ($context["continue_url"] ?? null), "html", null, true);
        echo "&enabled=1\" class=\"button button-primary submit mc-enabled\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "enable", []), "html", null, true);
        echo "</a>
    <a href=\"";
        // line 16
        echo \WPML\Core\twig_escape_filter($this->env, ($context["continue_url"] ?? null), "html", null, true);
        echo "&enabled=0\" class=\"button button-primary submit mc-disabled\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "continue", []), "html", null, true);
        echo "</a>
</p>
</span>
";
    }

    public function getTemplateName()
    {
        return "/setup/multi-currency.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  81 => 16,  75 => 15,  69 => 14,  60 => 10,  56 => 9,  52 => 8,  48 => 7,  42 => 4,  37 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<span id=\"{{ strings.step_id }}\">
<h1>{{ strings.heading }}</h1>

<div><strong>{{ strings.description }}</strong></div>

<ul>
    <li>{{ strings.bullet1 }}</li>
    <li>{{ strings.bullet2 }}</li>
    <li>{{ strings.bullet3 }}</li>
    <li><a target=\"_blank\" class=\"wpml-external-link\" rel=\"noopener\" href=\"{{ documentation_url }}\">{{ strings.bullet4 }}</a></li>
</ul>

<p class=\"wcml-setup-actions step\">
    <a href=\"{{ go_back_url }}\" class=\"go-back\">{{ strings.go_back }}</a>
    <a href=\"{{ continue_url }}&enabled=1\" class=\"button button-primary submit mc-enabled\">{{ strings.enable }}</a>
    <a href=\"{{ continue_url }}&enabled=0\" class=\"button button-primary submit mc-disabled\">{{ strings.continue }}</a>
</p>
</span>
", "/setup/multi-currency.twig", "/var/www/bike.meihao.shopping/htdocs/wp-content/plugins/woocommerce-multilingual/templates/setup/multi-currency.twig");
    }
}
