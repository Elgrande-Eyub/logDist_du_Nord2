<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\articleCategory;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleCategoryController extends Controller
{
    // This function returns all categories
    public function index()
    {

        try {

            $CategoryArticle = articleCategory::all();
            return response()->json($CategoryArticle);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);

        }
    }

    // This function returns all articles with the given category ID
    public function getCategoryByid($id)
    {
        try {
            $CategorySelected = Article::where('category_id', $id)->get();

            if(!$CategorySelected) {

                return response()->json([
                    'message' => 'Produits de cette catégorie Non trouvé'
                ], 404);

            }

            return response()->json($CategorySelected);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);

        }
    }

    // This function creates a new category
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            if (!$request->filled('category')) {
                return response()->json([
                    'message' => 'La catégorie ne peut pas être vide'
                ], 400);
            }

            $found = articleCategory::where('category', $request->category)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'La catégorie ne peut pas être dupliquée'
                ], 400);
            }

            $Added = articleCategory::create([
                'category' => $request->category
            ]);


            if (!$Added) {
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            DB::commit();
            return response()->json([
                    'message' => 'Catégorie créée avec succès',
                    'id' => $Added->id
                ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);
        }
    }

    // This function returns a single category by ID
    public function show($id)
    {
        try {
            $FoundedCategory = ArticleCategory::find($id);

            if (!$FoundedCategory) {
                return response()->json([
                    'message' => 'Catégorie introuvable'
                ], 404);
            }

            return response()->json([
                'Category Requested' => $FoundedCategory
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);
        }
    }

      // Update an article category
      public function update(Request $request, $id)
      {
          DB::beginTransaction();
          try {
              if (!$request->filled('category')) {
                  return response()->json([
                      'message' => 'La catégorie ne peut pas être vide'
                  ], 400);
              }

              $categoryFounded = articleCategory::find($id);

              if (!$categoryFounded) {
                  return response()->json([
                      'message' => 'Catégorie introuvable'
                  ], 404);
              }

              $categoryFounded->category = $request->input('category');
              $categoryFounded->save();

              DB::commit();
              return response()->json([
                  'message' => 'Catégorie mise à jour avec succès',
                  'id' => $categoryFounded
              ]);
          } catch (Exception $e) {
              DB::rollBack();
              return response()->json([
                  'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
              ], 400);
          }
      }

      // Delete an article category
      public function destroy($id)
      {
          DB::beginTransaction();
          try {
              $categoryFounded = articleCategory::find($id);

              if (!$categoryFounded) {
                  return response()->json([
                      'message' => 'Catégorie introuvable'
                  ], 404);
              }

              $categoryFounded->delete();

              DB::commit();
              return response()->json([

                 'id' => $categoryFounded
              ]);
          } catch (Exception $e) {
              DB::rollBack();
              return response()->json([
                  'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
              ], 400);
          }
      }







}
