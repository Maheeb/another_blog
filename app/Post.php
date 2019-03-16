<?php
namespace App;
use Carbon\Carbon;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Category;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Post
 *
 * @property int $id
 * @property int $author_id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $body
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property-read \App\User $author
 * @property-read mixed $date
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post latestFirst()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post published()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post wherePublishedAt($value)
 * @property int|null $category_id
 * @property-read \App\Category|null $category
 * @property-read mixed $body_html
 * @property-read mixed $excerpt_html
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereCategoryId($value)
 */
class Post extends Model
{
    //
    use softDeletes;
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'published_at', 'category_id','view_count','image'];
    protected $dates    = ['published_at','created_at', 'updated_at'];
    public function author()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = $value ?: NULL;
    }


        public function getDateAttribute($value)
    {
        return is_null($this->published_at) ? '' : $this->published_at->diffForHumans();
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopePublished($query)
    {
        return $query->where("published_at", "<=", Carbon::now());
    }
    public function getBodyHtmlAttribute($value)
    {
        return $this->body ? Markdown::convertToHtml(e($this->body)) : NULL;
    }
    public function getExcerptHtmlAttribute($value)
    {
        return $this->excerpt ? Markdown::convertToHtml(e($this->excerpt)) : NULL;
    }
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }
    public function scopeScheduled($query)
    {
        return $query->where("published_at", ">", Carbon::now());
    }

    public function scopeDraft($query)
    {
        return $query->whereNull("published_at");
    }

    public function dateFormatted($showTimes = false)
    {
        $format = "d/m/Y";
        if ($showTimes) $format = $format . " H:i:s";
        return $this->created_at->format($format);
    }
    public function publicationLabel()
    {
        if ( ! $this->published_at) {
            return '<span class="label label-warning">Draft</span>';
        }
        elseif ($this->published_at && $this->published_at->isFuture()) {
            return '<span class="label label-info">Schedule</span>';
        }
        else {
            return '<span class="label label-success">Published</span>';
        }
    }
    public function scopeFilter($query, $filter)
    {
        if (isset($filter['month']) && $month = $filter['month']) {
            $query->whereRaw('month(published_at) = ?', [Carbon::parse($month)->month]);
        }

        if (isset($filter['year']) && $year = $filter['year']) {
            $query->whereRaw('year(published_at) = ?', [$year]);
        }

        // check if any term entered
        if (isset($filter['term']) && $term = $filter['term'])
        {
            $query->where(function($q) use ($term) {
                // $q->whereHas('author', function($qr) use ($term) {
                //     $qr->where('name', 'LIKE', "%{$term}%");
                // });
                // $q->orWhereHas('category', function($qr) use ($term) {
                //     $qr->where('title', 'LIKE', "%{$term}%");
                // });
                $q->orWhere('title', 'LIKE', "%{$term}%");
                $q->orWhere('excerpt', 'LIKE', "%{$term}%");
            });
        }
    }
    public function tags(){

        return $this->belongsToMany(Tag::class);
    }
    public function getTagsHtmlAttribute()
    {
        $anchors = [];
        foreach($this->tags as $tag) {
            $anchors[] = '<a href="' . route('tag', $tag->slug) . '">' . $tag->name . '</a>';
        }
        return implode(", ", $anchors);
    }
    public static function archives()
    {
        return static::selectRaw('count(id) as post_count, year(published_at) year, monthname(published_at) month')
            ->published()
            ->groupBy('year', 'month')
            ->orderByRaw('min(published_at) desc')
            ->get();
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function commentsNumber($label = 'Comment')
    {
        $commentsNumber = $this->comments->count();

        return $commentsNumber . " " . str_plural($label, $commentsNumber);
    }

    public function createComment(array $data)
    {
        $this->comments()->create($data);
    }
    public function createTags($str)
    {
        $tags = explode(",", $str);
        $tagIds = [];

        foreach ($tags as $tag)
        {
            $newTag = Tag::firstOrCreate([
                'slug' => str_slug($tag),
                'name' => ucwords(trim($tag))
            ]);

            $tagIds[] = $newTag->id;
        }

        $this->tags()->sync($tagIds);
    }

    public function getTagsListAttribute()
    {
        return $this->tags->pluck('name');
    }

}
